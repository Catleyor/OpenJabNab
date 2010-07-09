#include <QCoreApplication>
#include <QDir>
#include <QLibrary>
#include <QPluginLoader>
#include <QString>
#include "apimanager.h"
#include "account.h"
#include "httprequest.h"
#include "log.h"
#include "pluginmanager.h"
#include <iostream>

PluginManager::PluginManager()
{
	// Load all plugins
	pluginsDir = QCoreApplication::applicationDirPath();
	pluginsDir.cd("plugins");
}

PluginManager & PluginManager::Instance()
{
  static PluginManager p;
  return p;
}

void PluginManager::InitApiCalls()
{
	DECLARE_API_CALL("getListOfPlugins", &PluginManager::Api_GetListOfPlugins);
	DECLARE_API_CALL("getListOfEnabledPlugins", &PluginManager::Api_GetListOfEnabledPlugins);
	DECLARE_API_CALL("getListOfBunnyPlugins", &PluginManager::Api_GetListOfBunnyPlugins);
	DECLARE_API_CALL("getListOfSystemPlugins", &PluginManager::Api_GetListOfSystemPlugins);
	DECLARE_API_CALL("getListOfRequiredPlugins", &PluginManager::Api_GetListOfRequiredPlugins);
	DECLARE_API_CALL("getListOfBunnyEnabledPlugins", &PluginManager::Api_GetListOfBunnyEnabledPlugins);
	DECLARE_API_CALL("activatePlugin", &PluginManager::Api_ActivatePlugin);
	DECLARE_API_CALL("deactivatePlugin", &PluginManager::Api_DeactivatePlugin);
	DECLARE_API_CALL("loadPlugin", &PluginManager::Api_LoadPlugin);
	DECLARE_API_CALL("unloadPlugin", &PluginManager::Api_UnloadPlugin);
	DECLARE_API_CALL("reloadPlugin", &PluginManager::Api_ReloadPlugin);
}

void PluginManager::UnloadPlugins()
{
	foreach(PluginInterface * p, listOfPlugins)
		delete p;
	foreach(QPluginLoader * l, listOfPluginsLoader.values())
	{
		l->unload();
		delete l;
	}
}

void PluginManager::LoadPlugins()
{
	Log::Info(QString("Finding plugins in : %1").arg(pluginsDir.path()));
	foreach (QString fileName, pluginsDir.entryList(QDir::Files)) 
		LoadPlugin(fileName);
}

bool PluginManager::LoadPlugin(QString const& fileName)
{
	if(listOfPluginsByFileName.contains(fileName))
	{
		Log::Error(QString("Plugin '%1' already loaded !").arg(fileName));
		return false;
	}

	QString file = pluginsDir.absoluteFilePath(fileName);
	if (!QLibrary::isLibrary(file))
		return false;

	QString status = QString("Loading %1 : ").arg(fileName);
	
	QPluginLoader * loader = new QPluginLoader(file);
	QObject * p = loader->instance();
	PluginInterface * plugin = qobject_cast<PluginInterface *>(p);
	if (plugin)
	{
		if(plugin->Init() == false)
		{
			delete plugin;
			loader->unload();
			delete loader;

			status.append(QString("%1 OK, Initialisation failed").arg(plugin->GetName())); 
			Log::Info(status);
			return false;
		}
		
		listOfPlugins.append(plugin);
		listOfPluginsFileName.insert(plugin, fileName);
		listOfPluginsLoader.insert(plugin, loader);
		listOfPluginsByName.insert(plugin->GetName(), plugin);
		listOfPluginsByFileName.insert(fileName, plugin);
		if(plugin->GetType() != PluginInterface::BunnyPlugin)
			listOfSystemPlugins.append(plugin);
		else
			BunnyManager::PluginLoaded(plugin);

		// Init Api Calls
		plugin->InitApiCalls();

		status.append(QString("%1 OK, Enable : %2").arg(plugin->GetName(),plugin->GetEnable() ? "Yes" : "No"));
		Log::Info(status);
		return true;
	}
	status.append("Failed, ").append(loader->errorString()); 
	Log::Info(status);
	return false;
}

bool PluginManager::UnloadPlugin(QString const& name)
{
	if(listOfPluginsByName.contains(name))
	{
		PluginInterface * p = listOfPluginsByName.value(name);
		if(p->GetType() == PluginInterface::BunnyPlugin)
			BunnyManager::PluginUnloaded(p);
		QString fileName = listOfPluginsFileName.value(p);
		QPluginLoader * loader = listOfPluginsLoader.value(p);
		listOfPluginsByFileName.remove(fileName);
		listOfPluginsFileName.remove(p);
		listOfPluginsLoader.remove(p);
		listOfPluginsByName.remove(name);
		listOfPlugins.removeAll(p);
		listOfSystemPlugins.removeAll(p);
		delete p;
		loader->unload();
		delete loader;
		Log::Info(QString("Plugin %1 unloaded.").arg(name));
		return true;
	}
	Log::Info(QString("Can't unload plugin %1").arg(name));
	return false;
}

bool PluginManager::ReloadPlugin(QString const& name)
{
	if(listOfPluginsByName.contains(name))
	{
		PluginInterface * p = listOfPluginsByName.value(name);
		QString file = listOfPluginsFileName.value(p);
		return (UnloadPlugin(name) && LoadPlugin(file));
	}
	return false;
}

/**************************************************/
/* HTTP requests are sent to ALL 'active' plugins */
/**************************************************/
void PluginManager::HttpRequestBefore(HTTPRequest const& request)
{
	// Call RequestBefore for all plugins
	foreach(PluginInterface * plugin, listOfPlugins)
		if(plugin->GetEnable())
			plugin->HttpRequestBefore(request);
}

bool PluginManager::HttpRequestHandle(HTTPRequest & request)
{
	// Call GetAnswer for all plugins until one returns true
	foreach(PluginInterface * plugin, listOfPlugins)
	{
		if(plugin->GetEnable() && plugin->HttpRequestHandle(request))
			return true;
	}
	return false;
}

void PluginManager::HttpRequestAfter(HTTPRequest const& request)
{
	// Call RequestAfter for all plugins
	foreach(PluginInterface * plugin, listOfPlugins)
		if(plugin->GetEnable())
			plugin->HttpRequestAfter(request);
}

/*****************************************************/
/* Others requests are sent only to "system" plugins */
/*****************************************************/
// Bunny -> Violet Message
void PluginManager::XmppBunnyMessage(Bunny * b, QByteArray const& data)
{
	foreach(PluginInterface * plugin, listOfSystemPlugins)
		if(plugin->GetEnable())
			plugin->XmppBunnyMessage(b, data);
}

// Violet -> Bunny Message
void PluginManager::XmppVioletMessage(Bunny * b, QByteArray const& data)
{
	foreach(PluginInterface * plugin, listOfSystemPlugins)
		if(plugin->GetEnable())
			plugin->XmppVioletMessage(b, data);
}

// Violet -> Bunny Packet
// Send the packet to all 'system' plugins, if one returns true, the message will be dropped !
bool PluginManager::XmppVioletPacketMessage(Bunny * b, Packet const& p)
{
	bool drop = false;
	foreach(PluginInterface * plugin, listOfSystemPlugins)
		if(plugin->GetEnable())
			drop |= plugin->XmppVioletPacketMessage(b, p);
	return drop;
}

// Bunny OnClick
bool PluginManager::OnClick(Bunny * b, PluginInterface::ClickType type)
{
	// Call OnClick for all 'system' plugins until one returns true
	foreach(PluginInterface * plugin, listOfSystemPlugins)
	{
		if(plugin->GetEnable())
		{
			if(plugin->OnClick(b, type))
				return true;
		}
	}
	return false;
}

bool PluginManager::OnEarsMove(Bunny * b, int left, int right)
{
	// Call OnEarsMove for all 'system' plugins until one returns true
	foreach(PluginInterface * plugin, listOfSystemPlugins)
	{
		if(plugin->GetEnable())
		{
			if(plugin->OnEarsMove(b, left, right))
				return true;
		}
	}
	return false;
}

bool PluginManager::OnRFID(Bunny * b, QByteArray const& id)
{
	// Call OnRFID for all 'system' plugins until one returns true
	foreach(PluginInterface * plugin, listOfSystemPlugins)
	{
		if(plugin->GetEnable())
		{
			if(plugin->OnRFID(b, id))
				return true;
		}
	}
	return false;
}

// Bunny Connect
void PluginManager::OnBunnyConnect(Bunny * b)
{
	foreach(PluginInterface * plugin, listOfSystemPlugins)
		if(plugin->GetEnable())
			plugin->OnBunnyConnect(b);
}

// Bunny Connect
void PluginManager::OnBunnyDisconnect(Bunny * b)
{
	foreach(PluginInterface * plugin, listOfSystemPlugins)
		if(plugin->GetEnable())
			plugin->OnBunnyDisconnect(b);
}

/*******/
/* API */
/*******/
API_CALL(PluginManager::Api_GetListOfPlugins)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Read))
		return new ApiManager::ApiError("Access denied");

	QMap<QString, QString> list;
	foreach (PluginInterface * p, listOfPlugins)
		list.insert(p->GetName(), p->GetVisualName());

	return new ApiManager::ApiMappedList(list);
}

API_CALL(PluginManager::Api_GetListOfEnabledPlugins)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Read))
		return new ApiManager::ApiError("Access denied");

	QList<QString> list;
	foreach (PluginInterface * p, listOfPlugins)
		if(p->GetEnable())
			list.append(p->GetName());

	return new ApiManager::ApiList(list);
}

API_CALL(PluginManager::Api_GetListOfBunnyPlugins)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Read))
		return new ApiManager::ApiError("Access denied");

	QList<QString> list;
	foreach (PluginInterface * p, listOfPlugins)
		if(p->GetType() == PluginInterface::BunnyPlugin)
			list.append(p->GetName());

	return new ApiManager::ApiList(list);
}

API_CALL(PluginManager::Api_GetListOfSystemPlugins)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Read))
		return new ApiManager::ApiError("Access denied");

	QList<QString> list;
	foreach (PluginInterface * p, listOfSystemPlugins)
		list.append(p->GetName());

	return new ApiManager::ApiList(list);
}

API_CALL(PluginManager::Api_GetListOfRequiredPlugins)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Read))
		return new ApiManager::ApiError("Access denied");

	QList<QString> list;
	foreach (PluginInterface * p, listOfPlugins)
		if(p->GetType() == PluginInterface::RequiredPlugin)
			list.append(p->GetName());

	return new ApiManager::ApiList(list);
}

API_CALL(PluginManager::Api_GetListOfBunnyEnabledPlugins)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Read))
		return new ApiManager::ApiError("Access denied");

	QList<QString> list;
	foreach (PluginInterface * p, listOfPlugins)
		if(p->GetType() == PluginInterface::BunnyPlugin && p->GetEnable())
			list.append(p->GetName());

	return new ApiManager::ApiList(list);
}

API_CALL(PluginManager::Api_ActivatePlugin)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Write))
		return new ApiManager::ApiError("Access denied");

	if(!hRequest.HasArg("name"))
		return new ApiManager::ApiError(QString("Missing 'name' argument<br />Request was : %1").arg(hRequest.toString()));

	PluginInterface * p = listOfPluginsByName.value(hRequest.GetArg("name"));
	if(!p)
		return new ApiManager::ApiError(QString("Unknown plugin '%1'<br />Request was : %2").arg(hRequest.GetArg("name"),hRequest.toString()));

	if(p->GetEnable())
		return new ApiManager::ApiError(QString("Plugin '%1' is already enabled!").arg(hRequest.GetArg("name")));

	p->SetEnable(true);
	return new ApiManager::ApiOk(QString("'%1' is now enabled").arg(p->GetName()));
}

API_CALL(PluginManager::Api_DeactivatePlugin)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Write))
		return new ApiManager::ApiError("Access denied");

	if(!hRequest.HasArg("name"))
		return new ApiManager::ApiError(QString("Missing 'name' argument<br />Request was : %1").arg(hRequest.toString()));

	PluginInterface * p = listOfPluginsByName.value(hRequest.GetArg("name"));
	if(!p)
		return new ApiManager::ApiError(QString("Unknown plugin '%1'<br />Request was : %2").arg(hRequest.GetArg("name"),hRequest.toString()));

	if(p->GetType() == PluginInterface::RequiredPlugin)
		return new ApiManager::ApiError(QString("Plugin '%1' can't be deactivated!").arg(hRequest.GetArg("name")));

	if(!p->GetEnable())
		return new ApiManager::ApiError(QString("Plugin '%1' is already disabled!").arg(hRequest.GetArg("name")));

	p->SetEnable(false);
	return new ApiManager::ApiOk(QString("'%1' is now disabled").arg(p->GetName()));
}

API_CALL(PluginManager::Api_UnloadPlugin)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Write))
		return new ApiManager::ApiError("Access denied");

	if(!hRequest.HasArg("name"))
		return new ApiManager::ApiError(QString("Missing 'name' argument<br />Request was : %1").arg(hRequest.toString()));

	QString name = hRequest.GetArg("name");
	if(UnloadPlugin(name))
	{
		return new ApiManager::ApiOk(QString("'%1' is now unloaded").arg(name));
	}
	else
		return new ApiManager::ApiError(QString("Can't unload '%1'!").arg(name));
}

API_CALL(PluginManager::Api_LoadPlugin)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Write))
		return new ApiManager::ApiError("Access denied");

	if(!hRequest.HasArg("filename"))
		return new ApiManager::ApiError(QString("Missing 'filename' argument<br />Request was : %1").arg(hRequest.toString()));

	QString filename = hRequest.GetArg("filename");
	if(LoadPlugin(filename))
		return new ApiManager::ApiOk(QString("'%1' is now loaded").arg(filename));
	else
		return new ApiManager::ApiError(QString("Can't load '%1'!").arg(filename));
}

API_CALL(PluginManager::Api_ReloadPlugin)
{
	Q_UNUSED(hRequest);

	if(!account.HasPluginsAccess(Account::Write))
		return new ApiManager::ApiError("Access denied");

	if(!hRequest.HasArg("name"))
		return new ApiManager::ApiError(QString("Missing 'name' argument<br />Request was : %1").arg(hRequest.toString()));

	QString name = hRequest.GetArg("name");
	if(ReloadPlugin(name))
		return new ApiManager::ApiOk(QString("'%1' is now reloaded").arg(name));
	else
		return new ApiManager::ApiError(QString("Can't reload '%1'!").arg(name));
}

/********************
 * Required Plugins *
 ********************/
 
void PluginManager::RegisterAuthPlugin(PluginAuthInterface * p)
{
	if(!authPlugin)
		authPlugin = p;
	else
		Log::Warning("An authentication plugin is already registered.");
}

void PluginManager::UnregisterAuthPlugin(PluginAuthInterface * p)
{
	if(authPlugin == p)
		authPlugin = 0;
	else
		Log::Warning("Bad plugin during UnregisterAuthPlugin");
}
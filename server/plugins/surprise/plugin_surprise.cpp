#include <QMapIterator>
#include "plugin_surprise.h"
#include "bunny.h"
#include "cron.h"
#include "messagepacket.h"

Q_EXPORT_PLUGIN2(plugin_surprise, PluginSurprise)

#define RANDOMIZE(x) x

PluginSurprise::PluginSurprise():PluginInterface("surprise", "Send random mp3 at random intervals") {}

PluginSurprise::~PluginSurprise() {}

void PluginSurprise::createCron(Bunny * b)
{
	// Check Frequency
	unsigned int frequency = b->GetPluginSetting(GetName(), "frequency", (uint)0).toUInt();
	if(!frequency)
	{
		LogError(QString("Bunny '%1' has invalid frequency '%2'").arg(b->GetID(), QString::number(frequency)));
		return;
	}
	
	// Register cron
	Cron::RegisterOneShot(this, RANDOMIZE(frequency), b, QVariant(), NULL);
}

void PluginSurprise::OnBunnyConnect(Bunny * b)
{
	createCron(b);
}

void PluginSurprise::OnBunnyDisconnect(Bunny * b)
{
	Cron::UnregisterAllForBunny(this, b);
}

void PluginSurprise::OnCron(Bunny * b, QVariant)
{
	if(b->IsIdle())
	{
		QByteArray file;
		// Fetch available files
		QDir * dir = GetLocalHTTPFolder();
		if(dir)
		{
			QString surprise = b->GetPluginSetting(GetName(), "folder", QString()).toString();
			
			if(!surprise.isNull() && dir->cd(surprise))
			{
				QStringList list = dir->entryList(QDir::Files|QDir::NoDotAndDotDot);
				if(list.count())
				{
					file = GetBroadcastHTTPPath(QString("%1/%3").arg(surprise, list.at(qrand()%list.count())));
					QByteArray message = "MU "+file+"\nPL 3\nMW\n";
					b->SendPacket(MessagePacket(message));
				}
			}
			else
				LogError("Invalid surprise config");

			delete dir;
		}
		else
			LogError("Invalid GetLocalHTTPFolder()");
	}
	// Restart Timer
	createCron(b);
}

/*******
 * API *
 *******/
 
void PluginSurprise::InitApiCalls()
{
	DECLARE_PLUGIN_BUNNY_API_CALL("setFolder(name)", PluginSurprise, Api_SetFolder);
	DECLARE_PLUGIN_BUNNY_API_CALL("getFolderList()", PluginSurprise, Api_GetFolderList);
	DECLARE_PLUGIN_BUNNY_API_CALL("setFrequency(value)", PluginSurprise, Api_SetFrequency);
}

PLUGIN_BUNNY_API_CALL(PluginSurprise::Api_SetFolder)
{
	Q_UNUSED(account);
	
	QString folder = hRequest.GetArg("name");
	if(availableSurprises.contains(folder))
	{
		// Save new config
		bunny->SetPluginSetting(GetName(), "folder", folder);

		return new ApiManager::ApiOk(QString("Folder changed to '%1'").arg(folder));
	}
	return new ApiManager::ApiError(QString("Unknown '%1' folder").arg(folder));
}

PLUGIN_BUNNY_API_CALL(PluginSurprise::Api_SetFrequency)
{
	Q_UNUSED(account);

	bunny->SetPluginSetting(GetName(), "frequency", QVariant(hRequest.GetArg("value").toInt()));
	OnBunnyDisconnect(bunny);
	OnBunnyConnect(bunny);
	return new ApiManager::ApiOk(QString("Plugin configuration updated."));
}

PLUGIN_BUNNY_API_CALL(PluginSurprise::Api_GetFolderList)
{
	Q_UNUSED(account);
	Q_UNUSED(bunny);
	Q_UNUSED(hRequest);

	// Check available folders and cache them
	QDir * httpFolder = GetLocalHTTPFolder();
	if(httpFolder)
	{
		availableSurprises = httpFolder->entryList(QDir::Dirs|QDir::NoDotAndDotDot);
		delete httpFolder;
	}

	return new ApiManager::ApiList(availableSurprises);
}

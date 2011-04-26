#include <QTcpSocket>
#include <QString>

#include "openjabnab.h"
#include "accountmanager.h"
#include "bunny.h"
#include "bunnymanager.h"
#include "ztamp.h"
#include "ztampmanager.h"
#include "httphandler.h"
#include "log.h"
#include "netdump.h"
#include "pluginmanager.h"
#include "settings.h"
#include "ttsmanager.h"
#include "xmpphandler.h"

OpenJabNab::OpenJabNab(int argc, char ** argv):QCoreApplication(argc, argv)
{
	GlobalSettings::Init();
	LogInfo("-- OpenJabNab Start --");
	TTSManager::Init();
	BunnyManager::Init();
	Bunny::Init();
	ZtampManager::Init();
	Ztamp::Init();
	AccountManager::Init();
	NetworkDump::Init();
	PluginManager::Init();
	BunnyManager::LoadBunnies();

	if(GlobalSettings::Get("Config/HttpListener", true) == true)
	{
		// Create Listeners
		httpListener = new QTcpServer(this);
		httpListener->listen(QHostAddress::LocalHost, GlobalSettings::GetInt("OpenJabNabServers/ListeningHttpPort", 8080));
		connect(httpListener, SIGNAL(newConnection()), this, SLOT(NewHTTPConnection()));
	}
	else
		LogWarning("Warning : HTTP Listener is disabled !");

	if(GlobalSettings::Get("Config/XmppListener", true) == true)
	{
		xmppListener = new QTcpServer(this);
		xmppListener->listen(QHostAddress::Any, GlobalSettings::GetInt("OpenJabNabServers/XmppPort", 5222));
		connect(xmppListener, SIGNAL(newConnection()), this, SLOT(NewXMPPConnection()));
	}
	else
		LogWarning("Warning : XMPP Listener is disabled !");

	httpApi = GlobalSettings::Get("Config/HttpApi", true).toBool();
	httpViolet = GlobalSettings::Get("Config/HttpViolet", true).toBool();
	standAlone = GlobalSettings::Get("Config/StandAlone", true).toBool();
	LogInfo(QString("Parsing of HTTP Api is ").append((httpApi == true)?"enabled":"disabled"));
	LogInfo(QString("Parsing of HTTP Bunny messages is ").append((httpViolet == true)?"enabled":"disabled"));
	LogInfo(QString("Current mode is ").append((standAlone == true)?"standalone":"connected to Violet"));
}

void OpenJabNab::Close()
{
	emit Quit();
}

OpenJabNab::~OpenJabNab()
{
	LogInfo("-- OpenJabNab Close --");
	xmppListener->close();
	httpListener->close();
	NetworkDump::Close();
	AccountManager::Close();
	PluginManager::Close();
	ZtampManager::Close();
	BunnyManager::Close();
	GlobalSettings::Close();
}

void OpenJabNab::NewHTTPConnection()
{
	HttpHandler * h = new HttpHandler(httpListener->nextPendingConnection(), httpApi, httpViolet, standAlone);
	connect(this, SIGNAL(Quit()), h, SLOT(Disconnect()));
}

void OpenJabNab::NewXMPPConnection()
{
	XmppHandler * x = new XmppHandler(xmppListener->nextPendingConnection(), standAlone);
	connect(this, SIGNAL(Quit()), x, SLOT(Disconnect()));
}

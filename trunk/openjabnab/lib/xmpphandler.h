#ifndef _XMPPHANDLER_H_
#define _XMPPHANDLER_H_

#include <QByteArray>
#include <QList>
#include <QObject>
#include <QTcpSocket>
#include "global.h"
#include "packet.h"

class Bunny;
class PluginManager;
class OJN_EXPORT XmppHandler : public QObject
{
	Q_OBJECT
	
public:
	XmppHandler(QTcpSocket *);
	void WriteDataToBunny(QByteArray const& p);

protected:
	virtual ~XmppHandler() {};

private slots:
	void HandleBunnyXmppMessage();
	void HandleVioletXmppMessage();
	void Disconnect();
	void VioletConnected();

private:
	QList<QByteArray> XmlParse(QByteArray const&);
	void WriteToBunny(QByteArray const&);

	QTcpSocket * incomingXmppSocket;
	QTcpSocket * outgoingXmppSocket;
	PluginManager & pluginManager;
	Bunny * bunny;
	QByteArray msgQueue;

	QByteArray OjnXmppDomain;
	QByteArray VioletXmppDomain;
	
	static unsigned short msgNb;
};

#endif

#include "account.h"
#include "ztamp.h"
#include "ztampmanager.h"
#include "httprequest.h"
#include "log.h"

ZtampManager::ZtampManager()
{
	ztampsDir = QCoreApplication::applicationDirPath();
	if (!ztampsDir.cd("ztamps"))
	{
		if (!ztampsDir.mkdir("ztamps"))
		{
			LogError("Unable to create ztamps directory !\n");
			exit(-1);
		}
		ztampsDir.cd("ztamps");
	}
}


ZtampManager & ZtampManager::Instance()
{
  static ZtampManager z;
  return z;
}

void ZtampManager::LoadAllZtamps()
{
	LogInfo(QString("Finding ztamps in : %1").arg(ztampsDir.path()));
	QStringList filters;
	filters << "*.dat";
	ztampsDir.setNameFilters(filters);
	foreach (QFileInfo file, ztampsDir.entryInfoList(QDir::Files))
	{
		GetZtamp(file.baseName().toAscii());
	}
}

void ZtampManager::DeleteZtamp(QByteArray const& ID) {
	Ztamp *z = GetZtamp(ID);
	if(z != NULL) {
		LogInfo(QString("Deleted Ztamp: %1").arg(QString(z->GetID())));
		listOfZtamps.remove(QByteArray::fromHex(z->GetID()));
		QFile ztampFile(ztampsDir.absoluteFilePath(QString("%1.dat").arg(QString(z->GetID()))));
		delete z;
		if(ztampFile.exists())
			ztampFile.remove();
	}
}

void ZtampManager::InitApiCalls()
{
	DECLARE_API_CALL("getListOfZtamps()", &ZtampManager::Api_GetListOfZtamps);
	DECLARE_API_CALL("getListOfAllZtamps()", &ZtampManager::Api_GetListOfAllZtamps);
	DECLARE_API_CALL("getListOfAllZtampsOwners()", &ZtampManager::Api_GetListOfAllZtampsOwners);
	DECLARE_API_CALL("removeZtamp(serial)", &ZtampManager::Api_RemoveZtamp);
}

int ZtampManager::GetZtampCount()
{
	return listOfZtamps.count();
}

Ztamp * ZtampManager::GetZtamp(QByteArray const& ztampHexID)
{
	QByteArray ztampID = QByteArray::fromHex(ztampHexID);
	if(ztampID == "")
		return NULL;

	if(listOfZtamps.contains(ztampID))
		return listOfZtamps.value(ztampID);

	Ztamp * z = new Ztamp(ztampID);
	listOfZtamps.insert(ztampID, z);
	return z;
}

Ztamp * ZtampManager::GetZtamp(PluginInterface * p, QByteArray const& ztampHexID)
{
	if(ztampHexID == "")
		return NULL;
	Ztamp * z = GetZtamp(ztampHexID);

	if(p->GetType() != PluginInterface::ZtampPlugin)
		return z;
	if(z->HasPlugin(p))
		return z;
	return NULL;
}

void ZtampManager::Close()
{
	foreach(Ztamp * z, listOfZtamps)
		delete z;
	listOfZtamps.clear();
}

QVector<Ztamp *> ZtampManager::GetZtamps()
{
	QVector<Ztamp *> list;
	foreach(Ztamp * z, listOfZtamps)
		list.append(z);
	return list;
}

void ZtampManager::PluginStateChanged(PluginInterface * p)
{
	foreach(Ztamp * z, listOfZtamps)
		z->PluginStateChanged(p);
}

void ZtampManager::PluginLoaded(PluginInterface * p)
{
	foreach(Ztamp * z, listOfZtamps)
		z->PluginLoaded(p);
}

void ZtampManager::PluginUnloaded(PluginInterface * p)
{
	foreach(Ztamp * z, listOfZtamps)
		z->PluginUnloaded(p);
}


API_CALL(ZtampManager::Api_GetListOfZtamps)
{
	Q_UNUSED(hRequest);

	if(!account.HasAccess(Account::AcZtamps,Account::Read))
		return new ApiManager::ApiError("Access denied");

	QMap<QString, QVariant> list;
	foreach(Ztamp * z, listOfZtamps)
		if(account.GetZtampsList().contains(z->GetID()))
			list.insert(z->GetID(), z->GetZtampName());

	return new ApiManager::ApiMappedList(list);
}

API_CALL(ZtampManager::Api_GetListOfAllZtamps)
{
	Q_UNUSED(hRequest);

	if(!account.IsAdmin())
		return new ApiManager::ApiError("Access denied");

	QMap<QString, QVariant> list;

	foreach(Ztamp * z, listOfZtamps)
		list.insert(z->GetID(), z->GetZtampName());

	return new ApiManager::ApiMappedList(list);
}

API_CALL(ZtampManager::Api_GetListOfAllZtampsOwners)
{
	Q_UNUSED(hRequest);

	if(!account.IsAdmin())
		return new ApiManager::ApiError("Access denied");

	QMap<QString, QVariant> list;

	foreach(Ztamp * z, listOfZtamps)
		list.insert(z->GetID(), z->GetGlobalSetting("OwnerAccount",""));

	return new ApiManager::ApiMappedList(list);
}

API_CALL(ZtampManager::Api_RemoveZtamp) {
	if(!account.HasAccess(Account::AcZtamps,Account::Write))
		return new ApiManager::ApiError("Access denied");

	QByteArray zID = hRequest.GetArg("serial").toAscii();

	DeleteZtamp(zID);
	return new ApiManager::ApiOk("Ztamp successfully deleted");
}

QHash<QByteArray, Ztamp *> ZtampManager::listOfZtamps;

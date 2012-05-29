#ifndef _HouseNumber_h_
#define _HouseNumber_h_

#include <QRegExp>
#include <QString>

// I now that this is not so nice… (if you have got a better idea [or a patch], let me know it)
extern bool bIgnoreCityHint, bCheckPostcodeNumber, bCheckStreetSuffix;
extern int iCheckPostcodeChars;
extern QString qsAssumeCountry, qsAssumeCity, qsAssumePostcode;

class HouseNumber;

typedef HouseNumber* pHouseNumber;

enum Completeness {
	COUNTRY=1,
	CITY=2,
	POSTCODE=4,
	STREET=8,
	NUMBER=16,
	HOUSENAME=32,
	ID=64,
	LAT=128,
	LON=256,
	IGNORE=512
};

class HouseNumber { 
	public:
		HouseNumber();
		~HouseNumber();
		
		void setCity(QString country);
		void setCountry(QString country);
		void setHousename(QString housename);
		void setId(int id);
		void setIgnore(bool ignore);
		void setIsWay(bool b);
		void setLat(double lat);
		void setLon(double lon);
		void setName(QString name);
		void setNumber(QString number);
		void setPostcode(QString postcode);
		void setStreet(QString street);
		void setShop(QString shop);
		
		int getId();
		bool getIsWay();
		double getLat();
		double getLon();
		
		bool isHouseNumber();
		bool isComplete();
		bool isBroken();
		
		QString qsGenerateDupeOutput();
		QString qsGenerateBrokenOutput();
		
		pHouseNumber dupe, left, right;
	
	private:
		double lat_, lon_;
		int id_, completeness_, broken_;
		bool ignore_, isHnr_, isWay_;
		QString city_, country_, housename_, name_, number_, postcode_, street_, shop_;
};

#endif 

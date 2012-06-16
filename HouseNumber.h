/*
housenumbervalidator Copyright (C) 2012 Markus Brenneis
This program comes with ABSOLUTELY NO WARRANTY.
This is free software, and you are welcome to redistribute it under certain conditions.
See main.cpp for details. */

#ifndef _HouseNumber_h_
#define _HouseNumber_h_

#include <QDebug>
#include <QRegExp>
#include <QString>

const double DISTANCE_THRESHOLD=0.01;

// I know that this is not so nice… (if you have got a better idea [or a patch], let me know it)
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
	HOUSENAME=32
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
		
		int getBroken() const;
		QString getCity() const;
		QString getCountry() const;
		int getId() const;
		bool getIgnore() const;
		bool getIsWay() const;
		double getLat() const;
		double getLon() const;
		QString getName() const;
		QString getNumber() const;
		QString getPostcode() const;
		QString getShop() const;
		QString getStreet() const;
		
		bool hasAddressInformation() const;
		bool isHouseNumber() const;
		bool isComplete();
		
		QString qsGenerateDupeOutput(bool possibleDupe=false) const;
		QString qsGenerateBrokenOutput() const;
		
		pHouseNumber dupe, left, right;
	
	private:
		double lat_, lon_;
		int id_, completeness_, broken_;
		bool ignore_, isHnr_, isWay_;
		QString city_, country_, housename_, name_, number_, postcode_, street_, shop_;
};

bool operator<(HouseNumber const& lhs, HouseNumber const rhs);
bool operator>(HouseNumber const& lhs, HouseNumber const rhs);
bool operator==(HouseNumber & lhs, HouseNumber & rhs);
inline double myAbs(double x) { return x<0 ? (-x) : x; }

#endif // _HouseNumber_h_

#ifndef _HouseNumber_h_
#define _HouseNumber_h_

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
		void setCountry(QString country);
		void setHousename(QString housename);
		void setId(int id);
		void setIgnore(bool ignore)
		void setIsWay(bool b);
		void setLat(double lat);
		void setLon(double lon);
		void setName(QString name);
		void setNumber(QString number);
		void setPostcode(QString postcode);
		void setStreet(QString street);
		void setShop();
		
		bool isHouseNumber();
		bool isComplete();
	
	private:
		double lat_, lon_;
		int id_;
		bool ignore_, isHnr_, isWay_;
		QString city_, country_, name_, number_, postcode_, street_, shop_;
		pBinTree dupe_, left_, right_;
		Completeness completeness_, broken_;
}

#endif 

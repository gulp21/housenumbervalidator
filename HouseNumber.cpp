/*
housenumbervalidator Copyright (C) 2012 Markus Brenneis
This program comes with ABSOLUTELY NO WARRANTY.
This is free software, and you are welcome to redistribute it under certain conditions.
See housenumbervalidator.cpp for details. */

#include "HouseNumber.h"

HouseNumber::HouseNumber() {
	lat_=0;
	lon_=0;
	id_=0;
	ignore_=false;
	isHnr_=false;
	isWay_=false;
	isEasyFix_=true;
	city_="";
	country_="";
	name_="";
	number_="";
	postcode_="";
	shop_="";
	street_="";
	suburb_="";
	dupe=NULL;
	left=NULL;
	right=NULL;
	completeness_=0;
	broken_=0;
	timeStamp_=QDate(2000,1,1);
}

HouseNumber::~HouseNumber() {
}

/*!
 * compares QString("%1%2%3%4%5%6%7")
 *  .arg(number_).arg(street_).arg(postcode_).arg(city_).arg(country_).arg(name_).arg(shop_).toLower()
 * thus, the house numbers are sorted by address (used for treeHousenumbers)
 * @note the address information must be complete
 */
bool HouseNumber::isLessThanAddress(HouseNumber const& rhs) const {
	if(getNumber().toLower()!=rhs.getNumber().toLower())
		return getNumber().toLower()<rhs.getNumber().toLower();
	if(getStreet().toLower()!=rhs.getStreet().toLower())
		return getStreet().toLower()<rhs.getStreet().toLower();
	if(getPostcode().toLower()!=rhs.getPostcode().toLower())
		return getPostcode().toLower()<rhs.getPostcode().toLower();
	if(getCity().toLower()!=rhs.getCity().toLower())
		return getCity().toLower()<rhs.getCity().toLower();
	if(getCountry().toLower()!=rhs.getCountry().toLower())
		return getCountry().toLower()<rhs.getCountry().toLower();
	if(getName().toLower()!=rhs.getName().toLower())
		return getName().toLower()<rhs.getName().toLower();
	return getShop().toLower()<rhs.getShop().toLower();
}

bool HouseNumber::isGreaterThanAddress(HouseNumber const& rhs) const {
	return rhs.isLessThanAddress(*this);
}

/*!
 * Two house numbers are considered to be equal if
 * * country, city, postcode, street, housenumber, name, and shop equal
 * * or housenumber, street, name, and shop equal, and country, city, and postcode do not differ (ignoring empty values),
 *    and lat/lon difference is less than DISTANCE_THRESHOLD
 */
bool HouseNumber::isSameAddress(HouseNumber const& rhs) const {
	if(getName().toLower()!=rhs.getName().toLower() ||
	   getShop().toLower()!=rhs.getShop().toLower() ||
	   getNumber().toLower()!=rhs.getNumber().toLower() ||
	   getStreet().toLower()!=rhs.getStreet().toLower() ||
	   getNumber()=="" || getStreet()=="") {
		return false;
	}
	
	if(getPostcode().toLower()==rhs.getPostcode().toLower() && getPostcode()!="" &&
	   getCity().toLower()==rhs.getCity().toLower() && getCity()!="" &&
	   getCountry().toLower()==rhs.getCountry().toLower() && getCountry()!="") {
		return true;
	}
	
	// consider two house numbers with similar address information and little distance to each other to be equal
	if(myAbs(getLat()-rhs.getLat())>DISTANCE_THRESHOLD ||
	   myAbs(getLon()-rhs.getLon())>DISTANCE_THRESHOLD)
		return false;
	if(getPostcode()!="" && rhs.getPostcode()!="" && getPostcode().toLower()!=rhs.getPostcode().toLower())
		return false;
	if(getCity()!="" && rhs.getCity()!="" && getCity().toLower()!=rhs.getCity().toLower())
		return false;
	if(getCountry()!="" && rhs.getCountry()!="" && getCountry().toLower()!=rhs.getCountry().toLower())
		return false;
	return true;
}

/*!
 * compares QString("%1%2%3%4")
 *  .arg(number_).arg(street_).arg(id_).arg(isWay_).toLower()
 * thus, the house numbers are sorted by address, id, and type (used for treeIncomplete)
 */
bool HouseNumber::isLessThanNode(HouseNumber const& rhs) const {
	if(getNumber().toLower()!=rhs.getNumber().toLower())
		return getNumber().toLower()<rhs.getNumber().toLower();
	if(getStreet().toLower()!=rhs.getStreet().toLower())
		return getStreet().toLower()<rhs.getStreet().toLower();
	if(getId()!=rhs.getId())
		return getId()<rhs.getId();
	return getIsWay()<rhs.getIsWay();
}

bool HouseNumber::isGreaterThanNode(HouseNumber const& rhs) const {
	return rhs.isLessThanNode(*this);
}

bool HouseNumber::isSameNode(HouseNumber const& rhs) const {
	return (id_==rhs.getId() && isWay_==rhs.getIsWay());
}

void HouseNumber::setCity(QString city) {
	city_=city;
	completeness_|=CITY;
	
	if( !city_[0].isUpper() || city_.contains("traße") || city_.endsWith("str") ||
	    city_.contains("str.") || city_.endsWith("Str") || city_.contains("Str.") ) {
		broken_|=CITY;
	}
}


void HouseNumber::setCountry(QString country) {
	country_=country;
	completeness_|=COUNTRY;
	
	if(country_.length()!=2 || !country_[0].isLetter() || !country_[1].isLetter() /*|| country_.toUpper()!=country_*/) {
		broken_|=COUNTRY;
	}
}

void HouseNumber::setHousename(QString housename) {
	housename_=housename;
	completeness_|=HOUSENAME;
	
	if(housename_.contains(QRegExp("^[0-9]+[aA-zZ]?$"))) {
		broken_|=HOUSENAME;
	}
}

void HouseNumber::setId(qint64 id) {
	id_=id;
}

void HouseNumber::setIgnore(bool ignore) {
	ignore_=ignore;
}

void HouseNumber::setIsWay(bool b) {
	isWay_=b;
}

void HouseNumber::setLat(double lat) {
	lat_=lat;
}

void HouseNumber::setLon(double lon) {
	lon_=lon;
}

void HouseNumber::setName(QString name) {
	if(name_=="")
		name_=name;
}

void HouseNumber::setNumber(QString number) {
	number_=number;
	completeness_|=NUMBER;
	
	if(number_.contains("traße") || number_.endsWith("str") || number_.contains("str.") ||
	   number_.endsWith("Str") || number_.contains("Str.") /*|| number_.contains(QRegExp("[0-9]+[Aa-Zz]?,? [0-9]+[Aa-Zz]?"))*/) {
		broken_|=NUMBER;
	} else if(number_.contains("<") || number_.contains("..") || number_.contains("?") ||
	          number_.contains("fix", Qt::CaseInsensitive) || number_.contains("unkn", Qt::CaseInsensitive) ||
	          QRegExp("[xX]+").exactMatch(number_)) {
		broken_|=NUMBER;
		isEasyFix_=false;
	}
}

void HouseNumber::setPostcode(QString postcode) {
	postcode_=postcode;
	completeness_|=POSTCODE;
	
	if(postcode_.contains("fix", Qt::CaseInsensitive) || postcode_.contains("unkn", Qt::CaseInsensitive)) {
		broken_|=POSTCODE;
		isEasyFix_=false;
	} else if( bCheckPostcodeNumber && ( (postcode_!=QString("%1").arg(postcode_.toInt()) &&
	    postcode_!=QString("0%1").arg(postcode_.toInt())) || postcode_.toInt()<=0 ) ) {
		broken_|=POSTCODE;
	} else if(iCheckPostcodeChars>-1 && postcode_.length()!=iCheckPostcodeChars) {
		broken_|=POSTCODE;
	}
}

void HouseNumber::setShop(QString shop) {
	shop_+=shop;
}

void HouseNumber::setStreet(QString street) {
	street_=street;
	completeness_|=STREET;
	
	if(bCheckStreetSuffix && (street_.endsWith("str") || street_.contains("str.") ||
	                          street_.endsWith("Str") || street_.contains("Str.")) ) {
		broken_|=STREET;
		if(street_.endsWith("tr."))
			isEasyFix_=false;
	} else if (street.contains(QRegExp("(str\\.|traße|weg) [0-9]+[a-z]?"))) {
		broken_|=STREET;
		isEasyFix_=true;
	} else if (street_.length()>0 && !street_[0].isUpper() && !street_.contains(QRegExp("[0-9](\\.|e)")) &&
	           !street_.startsWith("an") && !street_.startsWith("am") && !street_.startsWith("van") &&
	           !street_.startsWith("von") && !street_.startsWith("vom")) {
		broken_|=STREET;
		isEasyFix_=false;
	}
}

void HouseNumber::setSuburb(QString suburb) {
	suburb_+=suburb;
}

void HouseNumber::setTimeStamp(const int y, const int m, const int d) {
	timeStamp_=QDate(y,m,d);
}

void HouseNumber::setUid(QString uid) {
	uid_=uid;
}

int HouseNumber::getBroken() const {
	return broken_;
}

QString HouseNumber::getCity() const {
	return city_;
}

QString HouseNumber::getCountry() const {
	return country_;
}

qint64 HouseNumber::getId() const {
	return id_;
}

bool HouseNumber::getIgnore() const {
	return ignore_;
}

bool HouseNumber::getIsWay() const {
	return isWay_;
}

double HouseNumber::getLat() const {
	return lat_;
}

double HouseNumber::getLon() const {
	return lon_;
}

QString HouseNumber::getName() const {
	return name_;
}

QString HouseNumber::getNumber() const {
	return number_;
}

QString HouseNumber::getPostcode() const {
	return postcode_;
}

QString HouseNumber::getShop() const {
	return shop_;
}

QString HouseNumber::getStreet() const {
	return street_;
}

QString HouseNumber::getSuburb() const {
	return suburb_;
}

QDate HouseNumber::getTimeStamp() const {
	return timeStamp_;
}

QString HouseNumber::getUid() const {
	return uid_;
}

/*!
 * @returns false if the node does not have any addr:.* tags or essential information (id, lat, lon) is missing
 */
bool HouseNumber::hasAddressInformation() const {
	if(id_==0 || lat_==0 || lon_==0) return false;
	
	if(completeness_ & COUNTRY) return true;
	if( (completeness_ & CITY) && !bIgnoreCityHint) return true;
	if(completeness_ & POSTCODE) return true;
	if(completeness_ & STREET) return true;
	if(completeness_ & NUMBER) return true;
	if(completeness_ & HOUSENAME) return true;
	
	return false;
}

/*!
 * @returns false if the node does not have the addr:.* tags needed for operator== (i.e. number and street)
 * @note hasAddressInformation() should be called first
 */
bool HouseNumber::isHouseNumber() const {
	if( (completeness_ & NUMBER || completeness_ & HOUSENAME) && completeness_ & STREET)
		return true;
	return false;
}

/*!
 * @returns false if the house number does not have all pieces of information
 * if number is not set, number is set to housename
 * if name is not set, name is set to housename
 * @note isHouseNumer() should be called first
 */
bool HouseNumber::isComplete() {
	if(housename_!="") {
		if( !(completeness_ & NUMBER))
			setNumber(housename_);
		if(name_=="")
			setName(housename_);
	}
	
	if( country_=="" || city_=="" || postcode_=="" || street_=="" || number_=="" ) {
		//incompleteStream << qsGenerateIncompleteOutput(hnr, missingCount); TODO paramter
		//incompleteCount++;
		
		// TODO TODO ! how to handle not complete; not complete, but can be completed; complete
		if(country_=="") country_=qsAssumeCountry;
		if(city_=="") city_=qsAssumeCity;
		if(postcode_=="") postcode_=qsAssumePostcode;
		
		if(country_=="" || city_=="" || postcode_=="" || street_=="" || number_=="") {
			return false;
		}
	}
	
	return true;
}

QString HouseNumber::qsGenerateDupeOutput(bool possibleDupe) const {
	return QString("%1\t%2\t%3\t%4\t%5 %6\t%7\t%8\t%9\t%10\t%11\t%12\t%13\t%14\t%15\t%16\t%17\t%18\t%19\t%20\n")
	                .arg(lat_,0,'f',8).arg(lon_,0,'f',8).arg(id_)
	                .arg(isWay_?1:0).arg(name_==""?housename_:name_).arg(shop_)
	                .arg(country_).arg(city_).arg(postcode_).arg(street_).arg(number_).arg(housename_)
	                .arg(uid_)
	                .arg(dupe->getId()).arg(dupe->getIsWay()?1:0).arg(dupe->getLat(),0,'f',8).arg(dupe->getLon(),0,'f',8)
	                .arg(dupe->getUid())
	                .arg(possibleDupe?1:0)
	                .arg(timeStamp_.toString("yyyyMMdd"));
}

// QString qsGeneraateIncompleteOutput(housenumber hnr, int i) {
// 	//QString link=qsGenerateLink(hnr);
// 	//QString link="";
// 	
// 	//return QString("%1\t%2\tIncomplete\t%3 %4 %5 %6 %7 %8 is missing %9 pieces of address information\tpin.png\t16,16\t-8,-8\n")
// // 	                .arg(hnr.lat,0,'f',8).arg(hnr.lon,0,'f',8).arg(link)
// // 	                .arg(hnr.country).arg(hnr.city).arg(hnr.postcode).arg(hnr.street).arg(hnr.number)
// // 	                .arg(i);
// 	return "NULL";
// }

QString HouseNumber::qsGenerateBrokenOutput() const {
	return QString("%1\t%2\t%3\t%4\t%5\t%6 %7\t%8\t%9\t%10\t%11\t%12\t%13\t%14\t%15\t%16\n")
	                .arg(lat_,0,'f',8).arg(lon_,0,'f',8).arg(id_)
	                .arg(isWay_?1:0).arg(broken_).arg(name_==""?housename_:name_).arg(shop_)
	                .arg(country_).arg(city_).arg(postcode_).arg(street_).arg(number_).arg(housename_)
	                .arg(isEasyFix_?1:0).arg(uid_)
	                .arg(timeStamp_.toString("yyyyMMdd"));
}

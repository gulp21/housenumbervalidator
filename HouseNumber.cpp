#include "HouseNumber.h"

HouseNumber::HouseNumber() {
	lat_=0;
	lon_=0;
	id_=0;
	ignore_=false;
	isHnr_=false;
	isWay_=false;
	city_="";
	country_="";
	name_="";
	number_="";
	postcode_="";
	street_="";
	shop_="";
	dupe=NULL;
	left=NULL;
	right=NULL;
	completeness_=0;
	broken_=0;
}

HouseNumber::~HouseNumber() {
}

/*!
 * compares QString("%1%2%3%4%5%6")
 *  .arg(number_).arg(street_).arg(postcode_).arg(city_).arg(country_).arg(name_).arg(shop_).toLower()
 */
bool operator<(HouseNumber const& lhs, HouseNumber const rhs) {
	if(lhs.getNumber().toLower()!=rhs.getNumber().toLower())
		return lhs.getNumber().toLower()<rhs.getNumber().toLower();
	if(lhs.getStreet().toLower()!=rhs.getStreet().toLower())
		return lhs.getStreet().toLower()<rhs.getStreet().toLower();
	if(lhs.getPostcode().toLower()!=rhs.getPostcode().toLower())
		return lhs.getPostcode().toLower()<rhs.getPostcode().toLower();
	if(lhs.getCity().toLower()!=rhs.getCity().toLower())
		return lhs.getCity().toLower()<rhs.getCity().toLower();
	if(lhs.getCountry().toLower()!=rhs.getCountry().toLower())
		return lhs.getCountry().toLower()<rhs.getCountry().toLower();
	if(lhs.getName().toLower()!=rhs.getName().toLower())
		return lhs.getName().toLower()<rhs.getName().toLower();
	return lhs.getShop().toLower()<rhs.getShop().toLower();
}

bool operator>(HouseNumber const& lhs, HouseNumber const rhs) {
	if(lhs.getNumber().toLower()!=rhs.getNumber().toLower())
		return lhs.getNumber().toLower()>rhs.getNumber().toLower();
	if(lhs.getStreet().toLower()!=rhs.getStreet().toLower())
		return lhs.getStreet().toLower()>rhs.getStreet().toLower();
	if(lhs.getPostcode().toLower()!=rhs.getPostcode().toLower())
		return lhs.getPostcode().toLower()>rhs.getPostcode().toLower();
	if(lhs.getCity().toLower()!=rhs.getCity().toLower())
		return lhs.getCity().toLower()>rhs.getCity().toLower();
	if(lhs.getCountry().toLower()!=rhs.getCountry().toLower())
		return lhs.getCountry().toLower()>rhs.getCountry().toLower();
	if(lhs.getName().toLower()!=rhs.getName().toLower())
		return lhs.getName().toLower()>rhs.getName().toLower();
	return lhs.getShop().toLower()>rhs.getShop().toLower();
}

/*!
 * Two house numbers are considered to be equal if
 * * country, city, postcode, street, housenumber, name, and shop equal
 * * or housenumber, street, name, and shop equal, and country, city, and postcode do not differ (ignoring empty values),
 *    and lat/lon difference is less than DISTANCE_THRESHOLD
 */
bool operator==(HouseNumber & lhs, HouseNumber & rhs) {
	// only continue when we have got complete address information
	if(!lhs.isComplete() || !rhs.isComplete()) {
		return false;
	}
	
	// means that country, city, postcode, street, housenumber, and shop equal
	if(lhs<rhs && rhs>lhs) {
		return true;
	}
	
	if(lhs.getName().toLower()!=rhs.getName().toLower() || lhs.getShop().toLower()!=rhs.getShop().toLower()) {
		return false;
	}
	
	// consider two house numbers with similar address information and little distance to each other to be equal
	if(lhs.getNumber().toLower()==rhs.getNumber().toLower() && lhs.getStreet().toLower()==lhs.getStreet().toLower() && lhs.getNumber()!="" && lhs.getStreet()!="") {
		if(abs(lhs.getLat()-rhs.getLat())>DISTANCE_THRESHOLD || abs(lhs.getLon()-rhs.getLon())>DISTANCE_THRESHOLD)
			return false;
		if(lhs.getPostcode()!="" && rhs.getPostcode()!="" && lhs.getPostcode()!=rhs.getPostcode().toLower())
			return false;
		if(lhs.getCity()!="" && rhs.getCity()!="" && lhs.getCity()!=rhs.getCity().toLower())
			return false;
		if(lhs.getCountry()!="" && rhs.getCountry()!="" && lhs.getCountry()!=rhs.getCountry().toLower())
			return false;
		return true;
	}
	
	return false;
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
	
	if(housename_==QString("%1").arg(housename_.toInt())) {
		broken_|=HOUSENAME;
	}
}

void HouseNumber::setId(int id) {
	id_=id;
	completeness_|=ID;
}

void HouseNumber::setIgnore(bool ignore) {
	if(ignore)
		completeness_|=LON;
	else
		completeness_&=~LON;
}

void HouseNumber::setIsWay(bool b) {
	isWay_=b;
}

void HouseNumber::setLat(double lat) {
	lat_=lat;
	completeness_|=LAT;
}

void HouseNumber::setLon(double lon) {
	lon_=lon;
	completeness_|=LON;
}

void HouseNumber::setName(QString name) {
	if(name=="")
		name_=name;
}

void HouseNumber::setNumber(QString number) {
	number_=number;
	completeness_|=NUMBER;
	
	if( number_.contains("traße") || number_.endsWith("str") || number_.contains("str.") ||
	    number_.endsWith("Str") || number_.contains("Str.") /*|| number_.contains(QRegExp("[0-9]+[Aa-Zz]?,? [0-9]+[Aa-Zz]?"))*/ || number_.contains("<") || number_.contains("..") ||
	    number_.contains("fix", Qt::CaseInsensitive) || number_.contains("unkn", Qt::CaseInsensitive) ) {
		broken_|=NUMBER;
	}
}

void HouseNumber::setPostcode(QString postcode) {
	postcode_=postcode;
	completeness_|=POSTCODE;
	
	if( bCheckPostcodeNumber && ( (postcode_!=QString("%1").arg(postcode_.toInt()) &&
	    postcode_!=QString("0%1").arg(postcode_.toInt())) || postcode_.toInt()<=0 ) ) {
		broken_|=POSTCODE;
	} else if(iCheckPostcodeChars>-1 && postcode_.length()!=iCheckPostcodeChars) {
		broken_|=POSTCODE;
	}
}

void HouseNumber::setStreet(QString street) {
	street_=street;
	completeness_|=STREET;
	
	if( ( bCheckStreetSuffix && (street_.endsWith("str") || street_.contains("str.") ||
	                             street_.endsWith("Str") || street_.contains("Str.")) ) ||
	    ( street_.length()>0 && !street_[0].isUpper() && !street_.contains(QRegExp("[0-9](\\.|e)")) &&
	      !street_.startsWith("an") && !street_.startsWith("am") && !street_.startsWith("van") &&
	      !street_.startsWith("von") && !street_.startsWith("vom") ) ) {
		broken_|=STREET;
	}
}

void HouseNumber::setShop(QString shop) {
	shop_+=shop;
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

int HouseNumber::getId() const {
	return id_;
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

	//we use a given housename when there is no housenumber -> comparator & isComplete TODO
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
	// TODO hnrCount++;
}

/*!
 * @returns false if the node does not have the addr:.* tags needed for operator== (i.e. number and street)
 * NOTE hasAddressInformation() should be called first
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
 * NOTE isHouseNumer() should be called first
 */
bool HouseNumber::isComplete() {
	if(housename_!="") {
		if( !(completeness_ & NUMBER))
			setNumber(housename_);
		if(name_=="")
			setName(housename_);
	}
	
	if( (completeness_ & COUNTRY) && (completeness_ & CITY) && (completeness_ & POSTCODE) && (completeness_ & STREET) && (completeness_ & NUMBER) ) {
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

// TODO house housename if name==""

QString HouseNumber::qsGenerateDupeOutput() const {
	return QString("%1\t%2\t%3\t%4\t%5 %6\t%7\t%8\t%9\t%10\t%11\t%12\t%13\t%14\t%15\n")
	                .arg(lat_,0,'f',8).arg(lon_,0,'f',8).arg(id_)
	                .arg(isWay_?1:0).arg(name_==""?housename_:name_).arg(shop_)
	                .arg(country_).arg(city_).arg(postcode_).arg(street_).arg(number_)
	                .arg(dupe->getId()).arg(dupe->getIsWay()?1:0).arg(dupe->getLat(),0,'f',8).arg(dupe->getLon(),0,'f',8);
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
	return QString("%1\t%2\t%3\t%4\t%5\t%6 %7\t%8\t%9\t%10\t%11\t%12\n")
	                .arg(lat_,0,'f',8).arg(lon_,0,'f',8).arg(id_)
	                .arg(isWay_?1:0).arg(broken_).arg(name_==""?housename_:name_).arg(shop_)
	                .arg(country_).arg(city_).arg(postcode_).arg(street_).arg(number_);
}

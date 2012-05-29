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
}

HouseNumber::~HouseNumber() {
}

void HouseNumber::setHousename(QString housename) {
	housename_=housename;
	completeness_|=HOUSENAME;
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

void HouseNumber::setCity(QString city) {
	city_=city;
	completeness_|=CITY;
}


void HouseNumber::setCountry(QString country) {
	country_=country;
	completeness_|=COUNTRY;
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
}

void HouseNumber::setPostcode(QString postcode) {
	postcode_=postcode;
	completeness_|=POSTCODE;
}

void HouseNumber::setStreet(QString street) {
	street_=street;
	completeness_|=STREET;
}

void HouseNumber::setShop(QString shop) {
	shop_+=shop;
}

int HouseNumber::getId() {
	return id_;
}

bool HouseNumber::getIsWay() {
	return isWay_;
}

double HouseNumber::getLat() {
	return lat_;
}

double HouseNumber::getLon() {
	return lon_;
}


	//we use a given housename when there is no housenumber -> comparator & isComplete
/*!
 * @returns false if the node does not look like a housenumber or essential information is missing
 */
bool HouseNumber::isHouseNumber() {
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

	// TODO we probably should put the checks in the setter functions
bool HouseNumber::isBroken() {
	broken_=0;
	
	if(country_!="" && (country_.length()!=2 || !country_[0].isLetter() || !country_[1].isLetter() /*|| country_.toUpper()!=country_*/) ) {
		broken_|=COUNTRY;
	}
	
	if( city_.length()>0 && ( !city_[0].isUpper() || city_.contains("traße") ||
	   city_.endsWith("str") || city_.contains("str.") || city_.endsWith("Str") || city_.contains("Str.") ) ) {
		broken_|=CITY;
	}
	
	if(postcode_!="") {
		if(bCheckPostcodeNumber && ( (postcode_!=QString("%1").arg(postcode_.toInt()) && postcode_!=QString("0%1").arg(postcode_.toInt())) || postcode_.toInt()<=0) ) {
			broken_|=POSTCODE;
		} else if(iCheckPostcodeChars>-1 && postcode_.length()!=iCheckPostcodeChars) {
			broken_|=POSTCODE;
		}
	}
	
	if( ( bCheckStreetSuffix && (street_.endsWith("str") || street_.contains("str.") || street_.endsWith("Str") || street_.contains("Str.")) )
           || ( street_.length()>0 && !street_[0].isUpper() && !street_.contains(QRegExp("[0-9](\\.|e)")) && !street_.startsWith("an") && !street_.startsWith("am") && !street_.startsWith("van") && !street_.startsWith("von") && !street_.startsWith("vom") ) ) {
		broken_|=STREET;
	}
	
	if( number_.length()>0 && ( number_.contains("traße") || number_.endsWith("str") || number_.contains("str.") || number_.endsWith("Str") || number_.contains("Str.") /*|| number_.contains(QRegExp("[0-9]+[Aa-Zz]?,? [0-9]+[Aa-Zz]?")) || number_.contains("<") || number_.contains("fix", Qt::CaseInsensitive) || number_.contains("unkn", Qt::CaseInsensitive) || number_.contains("..")*/ ) ) {
		broken_|=NUMBER;
	}
	
	if(housename_==QString("0%1").arg(housename_.toInt())) {
		broken_|=HOUSENAME;
	}
	
	return (broken_==0);
	
	//TODO
// 		brokenStream << qsGenerateBrokenOutput(hnr, broken);
// 		brokenCount++;
	
}

// TODO house housename if name==""

QString HouseNumber::qsGenerateDupeOutput() {
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

QString HouseNumber::qsGenerateBrokenOutput() { // TODO broken_ : only interesting part
	return QString("%1\t%2\t%3\t%4\t%5\t%6 %7\t%8\t%9\t%10\t%11\t%12\n")
	                .arg(lat_,0,'f',8).arg(lon_,0,'f',8).arg(id_)
	                .arg(isWay_?1:0).arg(broken_).arg(name_==""?housename_:name_).arg(shop_)
	                .arg(country_).arg(city_).arg(postcode_).arg(street_).arg(number_);
}

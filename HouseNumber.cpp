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
	dupe_=NULL;
	left_=NULL;
	right_=NULL;
	completeness_=0;
}

void HouseNumber::~HouseNumber() {
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
	name_=name;
}

void setNumber(QString number) {
	number_=number;
	completeness_|=NUMBER;
}

void setPostcode(QString postcode) {
	postcode_=postcode;
	completeness_|=POSTCODE;
}

void setStreet(QString street) {
	street_=street;
	completeness_|=STREET;
}

void setShop(QString shop) {
	shop_+=shop;
}

	//we use a given housename when there is no housenumber -> comparator & isComplete
/*!
 * @returns false if the node does not look like a housenumber or essential information is missing
 */
bool HouseNumber::isHouseNumer() {
	if(hnr.id_==0 || lat_==0 || lon_==0) return false;
	
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
		if( !(completeness_ & NAME) )
			setName(housename_);
	}
	
	if( (completeness_ & COUNTRY) && (completeness_ & CITY) && (completeness_ & POSTCODE) && (completeness_ & STREET) && (completeness_ & NUMBER) ) {
		//incompleteStream << qsGenerateIncompleteOutput(hnr, missingCount); TODO paramter
		//incompleteCount++;
		
		// TODO TODO ! how to handle not complete; not complete, but can be completed; complete
		if(hnr.country=="") hnr.country=qsAssumeCountry;
		if(hnr.city=="") hnr.city=qsAssumeCity;
		if(hnr.postcode=="") hnr.city=qsAssumePostcode;
		
		if(hnr.country=="" || hnr.city=="" || hnr.postcode=="" || hnr.street=="" || hnr.number=="") {
			return false;
		}
	}
	
	return true;
	
	//TODO
	//set Assume values
}

bool HouseNumber::isBroken() {
	broken_=0;
	
	if(country!="" && (hnr.country.length()!=2 || !hnr.country[0].isLetter() || !hnr.country[1].isLetter() /*|| hnr.country.toUpper()!=hnr.country*/) ) {
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
		broken|=STREET;
	}
	
	if( number_.length()>0 && ( number_.contains("traße") || number_.endsWith("str") || number_.contains("str.") || number_.endsWith("Str") || number_.contains("Str.") /*|| number_.contains(QRegExp("[0-9]+[Aa-Zz]?,? [0-9]+[Aa-Zz]?")) || number_.contains("<") || number_.contains("fix", Qt::CaseInsensitive) || number_.contains("unkn", Qt::CaseInsensitive) || number_.contains("..")*/ ) {
		broken|=NUMBER;
	}
	
	if(housename_==QString("0%1").arg(housename_.toInt())) {
		broken|=HOUSENAME;
	}
	
	return broken==0;
	
	//TODO
// 		brokenStream << qsGenerateBrokenOutput(hnr, broken);
// 		brokenCount++;
	
}

	// TODO use housename if name==""

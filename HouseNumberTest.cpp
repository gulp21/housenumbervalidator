#include <QtTest/QtTest>
bool bIgnoreCityHint=false, bCheckPostcodeNumber=true, bCheckStreetSuffix=true;
int iCheckPostcodeChars=5;
QString qsAssumeCountry="", qsAssumeCity="Kaarst", qsAssumePostcode="";
#include "HouseNumber.h"

class HouseNumberTest: public QObject {
	Q_OBJECT
	private slots:
		void isBrokenTest() {
			HouseNumber *hnr = new HouseNumber();
			hnr->setCountry("Deutschland");
			QVERIFY(hnr->hasAddressInformation()==false);
			hnr->setLat(51.1970467);
			QVERIFY(hnr->getLat()==51.1970467);
			hnr->setLon(6.6081718);
			hnr->setId(1337);
			hnr->setIsWay(false);
			QVERIFY(hnr->hasAddressInformation()==true);
			QVERIFY(hnr->isHouseNumber()==false);
			hnr->setHousename("1a");
			hnr->setStreet("Rathausplatz");
			QVERIFY(hnr->isHouseNumber()==true);
			hnr->setPostcode("41564");
			QVERIFY(hnr->isComplete()==true);
			QVERIFY(hnr->getBroken()==33);
			QCOMPARE(hnr->qsGenerateBrokenOutput(),
				 QString("51.19704670\t6.60817180\t1337\t0\t33\t1a \tDeutschland\tKaarst\t41564\tRathausplatz\t1a\t1a\t1\n"));
			hnr->setStreet("Rathausstr.");
			QCOMPARE(hnr->qsGenerateBrokenOutput(),
				 QString("51.19704670\t6.60817180\t1337\t0\t41\t1a \tDeutschland\tKaarst\t41564\tRathausstr.\t1a\t1a\t0\n"));
		}
		
		void isSameAddressTest() {
			qsAssumeCity="";
			HouseNumber *hnr1 = new HouseNumber();
			hnr1->setLat(51.1970467);
			hnr1->setLon(6.6081718);
			hnr1->setId(1337);
			hnr1->setIsWay(true);
			hnr1->setCountry("DE");
			hnr1->setCity("Kaarst");
			hnr1->setPostcode("41564");
			hnr1->setStreet("Rathausplatz");
			hnr1->setNumber("4a");
			QVERIFY(hnr1->hasAddressInformation()==true);
			QVERIFY(hnr1->isHouseNumber()==true);
			QVERIFY(hnr1->isComplete()==true);
			QVERIFY(hnr1->getBroken()==0);
			HouseNumber *hnr2 = new HouseNumber();
			hnr2->setLat(51.205862);
			hnr2->setLon(6.5988064);
			hnr2->setId(7353);
			hnr2->setIsWay(false);
			hnr2->setCountry("DE");
			hnr2->setStreet("Rathausplatz");
			hnr2->setNumber("4A");
			QVERIFY(hnr2->hasAddressInformation()==true);
			QVERIFY(hnr2->isHouseNumber()==true);
			QVERIFY(hnr2->isComplete()==false);
			QVERIFY(hnr2->getBroken()==0);
			QVERIFY(hnr1->isSameAddress(*hnr2));
			hnr2->setLon(6.6181719);
			QVERIFY(!(hnr1->isSameAddress(*hnr2)));
			hnr1->setLat(51.2005576);
			hnr1->setLon(6.6880044);
			hnr1->setCountry("DE");
			hnr1->setCity("Neuss");
			hnr1->setPostcode("");
			hnr1->setStreet("Niederstraße");
			hnr1->setNumber("21");
			QVERIFY(hnr1->isLessThanAddress(*hnr2));
			hnr2->setLat(56.2555507);
			hnr2->setLon(6.6880471);
			hnr2->setCountry("DE");
			hnr2->setCity("Neuss");
			hnr2->setPostcode("12300");
			hnr2->setStreet("van-Niederstraße");
			hnr2->setNumber("231");
			QVERIFY(!(hnr1->isSameAddress(*hnr2)));
			hnr2->setPostcode("");
			hnr2->setStreet("Niederstraße");
			hnr2->setNumber("21");
			QVERIFY(!(hnr1->isSameAddress(*hnr2)));
		}
		
		void addressComparisonTest() {
			qsAssumeCity="";
			HouseNumber *hnr1 = new HouseNumber();
			HouseNumber *hnr2 = new HouseNumber();
			hnr1->setLat(51.2005576);
			hnr1->setLon(6.6880044);
			hnr1->setCountry("DE");
			hnr1->setCity("Neuss");
			hnr1->setPostcode("12345");
			hnr1->setStreet("Niederstraße");
			hnr1->setNumber("21");
			hnr2->setLat(56.2555507);
			hnr2->setLon(6.6880471);
			hnr2->setCountry("DE");
			hnr2->setCity("Neuss");
			hnr2->setPostcode("12300");
			hnr2->setStreet("van-Niederstraße");
			hnr2->setNumber("21");
			QVERIFY(hnr1->isLessThanAddress(*hnr2));
			QVERIFY(!(hnr1->isGreaterThanAddress(*hnr2)));
			hnr2->setPostcode("12345");
			hnr2->setStreet("Niederstraße");
			QVERIFY(!(hnr1->isLessThanAddress(*hnr2)));
			QVERIFY(!(hnr1->isGreaterThanAddress(*hnr2)));
			QVERIFY(hnr1->isSameAddress(*hnr2));
			hnr2->setShop("bus_stop");
			QVERIFY(hnr1->isLessThanAddress(*hnr2));
			QVERIFY(!(hnr1->isGreaterThanAddress(*hnr2)));
		}
		
		void nodeComparisonTest() {
			qsAssumeCity="";
			HouseNumber *hnr1 = new HouseNumber();
			HouseNumber *hnr2 = new HouseNumber();
			hnr1->setLat(51.2005576);
			hnr1->setLon(6.6880044);
			hnr1->setId(125);
			hnr1->setIsWay(false);
			hnr1->setStreet("Niederstraße");
			hnr1->setNumber("21a");
			hnr2->setLat(56.2555507);
			hnr2->setLon(6.6880471);
			hnr2->setId(125);
			hnr2->setIsWay(true);
			hnr2->setStreet("Niederstraße");
			hnr2->setNumber("21A");
			QVERIFY(hnr1->isLessThanNode(*hnr2));
			QVERIFY(!(hnr1->isGreaterThanNode(*hnr2)));
			hnr2->setId(124);
			hnr2->setIsWay(false);
			QVERIFY(!(hnr1->isLessThanNode(*hnr2)));
			QVERIFY(hnr1->isGreaterThanNode(*hnr2));
			QVERIFY(hnr2->isLessThanNode(*hnr1));
			hnr2->setId(125);
			QVERIFY(hnr1->isSameNode(*hnr2));
			QVERIFY(hnr2->isSameNode(*hnr1));
		}
};

QTEST_MAIN(HouseNumberTest)
#include "HouseNumberTest.moc"

/*
 * Outside WX Station
 
 *  Works great
 *  Sherman Stebbins
 *  12-18-2018
 
 Uses sensors and modules:
 Bmp280, DHT22, ADS1115, Custom light sensor, custom wind speed.
 Posting to web: temp(2 sensors, DHT22 and BMP280), Humidity, Pressure, light, wind speed
 
 *  Location: Back Deck.
 * 
 * Compile for 8266 12E or 12F
 * as wemos D1 mini
 * fast baud
 
 * Update 12-28-17 - Added DHT22
 * 
 * Updated 01-02-18 Added ads1115
 * for voltage measure and wind measure and light measure.
 * const for voltage divider 100k/33k is .2481203008 divide output voltage by const.
 * 
 * Updated 1-10-18 Added windspeed function for sensor that will be soon added
 * and wind chill function
 * Updated 1-17-18 Added power on power off for windspeed, so its not just consuming power
 * during sleep.
 * Updated 1-24-18 Added this file to github
 */

#include <ESP8266WiFi.h>
#include <DHT.h>
#include <Wire.h>
#include <SPI.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BMP280.h>
#include <Adafruit_ADS1015.h> //ads1115
Adafruit_ADS1115 ads;  /* Use this for the 16-bit version */
float volt = 0.0; // The result of applying the scale factor to the raw value
int lightLevel=0; //light level from 1(brightest) to 18000(darkest)

//#define DEBUG

#define led 2
#define DHTPIN 12
#define DHTTYPE DHT22
#define turn_on 0
#define turn_off 1
#define WIND 13
#define windSpeedSensorOnOff 14
//#define testPin 10

DHT dht(12, DHTTYPE);
Adafruit_BMP280 bme; // I2C

//IP or name of address root: ie: google.com
//NOT google.com/nothing/after/the/dotcom.html

const char* hostGet = "shermluge.xyz"; //http://



const char* ssid = "ssid";
const char* password = "password";


float ha = 0.0;
float ta = 0.0;
float fa = 0.0;
float tempBmp = 0.0;
double pressure = 0.0;
float kphWindSpeed;
int rpmWind;

int countavg = 0;

void setup() {
    Serial.begin(9600);
    Serial.println("DHTxx test!");
    pinMode(led,OUTPUT);
    pinMode(windSpeedSensorOnOff,OUTPUT);
    //pinMode(testPin,OUTPUT);
    dht.begin();
    //Serial.begin(115200);
    WiFiCon();
    if (!bme.begin()) {  
      Serial.println("Could not find a valid BMP280 sensor, check wiring!");
      //while (1);
    }
    /////ADS1115/////////////////////
    ads.begin();
    digitalWrite(windSpeedSensorOnOff,turn_off);
  // Setup 3V comparator on channel 0
  //ads.startComparator_SingleEnded(0, 1000);
}

void loop() {
  digitalWrite(led,turn_on);
  //digitalWrite(testPin,HIGH);
  digitalWrite(windSpeedSensorOnOff,HIGH);
  kphWindSpeed = getWindSpeed();
  delay(10);
  digitalWrite(windSpeedSensorOnOff,LOW);
  //delay(1000);
  //digitalWrite(testPin,LOW);
  //digitalWrite(testPin,LOW);
  readBmp280(); //must be before postData();
  /////ads1115////////////////////////////////
  int16_t adc0,adc1;
  float scalefactor = 0.1875F; // This is the scale factor for the default +/- 6.144 Volt Range we will use
    
  // Comparator will only de-assert after a read
  //adc0 = ads.getLastConversionResults(); //voltage
  adc0 = ads.readADC_SingleEnded(0);
  adc1 = ads.readADC_SingleEnded(1); //light level
  lightLevel = adc1;
  volt = (adc0 * scalefactor)/1000.0;
  volt = volt/.25; //more accurate const due to resistor variation
  Serial.print("AIN0: "); Serial.print(volt); Serial.print("   "); Serial.println(adc0);
  Serial.print("AIN1: ");  Serial.println(adc1);
  //////////////////////////////////////////////////////
  postData();
  //WiFi.forceSleepBegin(20000);
  //WiFi.forceSleepWake();
  //WiFi.forceSleepBegin(uint32 sleepUs = 60000);
  Serial.println("Going into deep sleep for 20 seconds");
  digitalWrite(led,turn_off);
  //ESP.deepSleep(60e6); // 20e6 is 20 seconds, 60e6 is 1 min.
  ESP.deepSleep(300e6); // 20e6 is 20 seconds
  //WiFi.forceSleepWake();
}


void readBmp280() {
    int avgCount = 0;
    for(int i = 1;i<=5;i++){
      tempBmp += bme.readTemperature();
      pressure += bme.readPressure();
      avgCount++;
    }
    tempBmp = tempBmp/avgCount;
    pressure = pressure/avgCount;
    
    float f = tempBmp * 1.8 + 32;
    double in = pressure * 0.0002952998;
    
    Serial.print("Temperature = ");
    Serial.print(tempBmp);
    Serial.println(" *C");    
    Serial.print(f);
    Serial.println(" F");    
    Serial.print("Pressure = ");    
    Serial.print(pressure);
    Serial.print("   inches: ");
    Serial.println(in);

    //0.000295301
    //0.0002952998

    Serial.print("Approx altitude = ");
    Serial.print(bme.readAltitude(1040.2)); // this should be adjusted to your local forcase (1013.25)  
    Serial.println(" m");
    
    Serial.println();
    delay(2000);
}

int WiFiCon() {
    // Check if we have a WiFi connection, if we don't, connect.
  int xCnt = 0;

  if (WiFi.status() != WL_CONNECTED){

        Serial.println();
        Serial.println();
        Serial.print("Connecting to ");
        Serial.println(ssid);

        WiFi.mode(WIFI_STA);
        
        WiFi.begin(ssid, password);
        
        while (WiFi.status() != WL_CONNECTED  && xCnt < 50) {
          delay(500);
          Serial.print(".");
          xCnt ++;
        }

        if (WiFi.status() != WL_CONNECTED){
          Serial.println("WiFiCon=0");
          return 0; //never connected
        } else {
          Serial.println("WiFiCon=1");
          Serial.println("");
          Serial.println("WiFi connected");  
          Serial.println("IP address: ");
          Serial.println(WiFi.localIP());
          return 1; //1 is initial connection
        }

  } else {
    Serial.println("WiFiCon=2");
    return 2; //2 is already connected
  
  }
}


void postData() {

   WiFiClient clientGet;
   const int httpGetPort = 80;

   //the path and file to send the data to:
   String urlGet = "/wx/WX.php";
   for(int i = 1;i<5;i++){
      countavg++;
      float h = dht.readHumidity();
      ha+=h;
      // Read temperature as Celsius (the default)
      float t = dht.readTemperature();
      ta+=t;
      // Read temperature as Fahrenheit (isFahrenheit = true)
      float f = dht.readTemperature(true);
      fa+=f;
      //result = analogRead(A0);
      //temp=temp+result;
      delay(1);
      // Check if any reads failed and exit early (to try again).
      if (isnan(h) || isnan(t) || isnan(f)) {
        Serial.println("Failed to read from DHT sensor!");
        countavg--;
        ha-=h;
        ta-=t;
        fa-=f;
        //return;
      }
  }
  ha=ha/countavg;
  ta=ta/countavg;
  fa=fa/countavg;
  countavg=0;

  // Compute heat index in Fahrenheit (the default)
  float hif = dht.computeHeatIndex(fa, ha);
  // Compute heat index in Celsius (isFahreheit = false)
  float hic = dht.computeHeatIndex(ta, ha, false);


 
  Serial.print("temp=   ");
  Serial.println(ta);
  Serial.print("F=   ");
  Serial.println(fa);
  //double tempf = tempc * 1.8 + 32; 
  String tempstring = "";
  tempstring += (" Humidity: " + String(ha));
  Serial.println(tempstring);
  urlGet += "?TempF=" + String(fa) + "&TempC=" + String(ta) + "&TempFi=" + String(hif) + "&TempCi=" + String(hic) + 
  "&Humidity=" + String(ha)+"&pressure=" + String(pressure) + "&tempBmp=" + String(tempBmp) + "&volt=" + String(volt) + 
  "&light=" + String(lightLevel) + "&windspeed=" + String(rpmWind);
   
      Serial.print(">>> Connecting to host: ");
      Serial.println(hostGet);
      
      if (!clientGet.connect(hostGet, httpGetPort)) {
          Serial.print("Connection failed: ");
          Serial.print(hostGet);
      } else {
          clientGet.println("GET " + urlGet + " HTTP/1.1");
          clientGet.print("Host: ");
          clientGet.println(hostGet);
          clientGet.println("User-Agent: ESP8266/1.0");
          clientGet.println("Connection: close\r\n\r\n");
          
          unsigned long timeoutP = millis();
          while (clientGet.available() == 0) {            
            if (millis() - timeoutP > 10000) {
              Serial.print(">>> Client Timeout: ");
              Serial.println(hostGet);
              clientGet.stop();
              return;
            }
          }
          //just checks the 1st line of the server response. Could be expanded if needed.
          while(clientGet.available()){
            String retLine = clientGet.readStringUntil('\r');
            Serial.println("return line from server:");
            Serial.println(retLine);
            break; 
          }
      } //end client connection if else  
      Serial.println(urlGet);                      
      Serial.print(">>> Closing host: ");
      Serial.println(hostGet);          
      clientGet.stop();
  
}

////Returns wind chill in F ////////////
float getWindChill(float TempF, float MPH){
    float windChill;
    if ((TempF <50.0) && (MPH > 3.0))
    {
        windChill=35.74+0.6215*TempF-
        35.75*pow(MPH,0.16)+0.4275*TempF*pow(MPH,0.16);
    }else{
        windChill=TempF;
    }
    return windChill;
}

////Returns wind speed in KPH/////
//#define WIND 13 //must be defined
float getWindSpeed(){ 
  unsigned long maxTime = 6000; //read revolutions * 2 for 6 seconds 
  unsigned long endMillis = millis()+maxTime;
  bool prevRead = LOW;
  //float circ = .0011938052;
  float circ = .0007539862;
  float rpm = 0;
  float kph = 0;
  int count = 0;
  
  #ifdef DEBUG
    Serial.println(endMillis);
    Serial.print(" start of getWindSpeed  millis:");
    Serial.println(millis());
  #endif
  
  int testcount=0;
  while(1){    
    if(endMillis<millis()){
        #ifdef DEBUG
            Serial.println("break"); 
        #endif
        break;
    }
    if(digitalRead(WIND)==HIGH && prevRead != HIGH){      
      prevRead = HIGH;
      count++;
      #ifdef DEBUG
        Serial.print("millis: ");
        Serial.println(millis());
        Serial.print("endMillis: ");
        Serial.println(endMillis);
        Serial.print("count: ");
        Serial.println(count);
      #endif
    }
    if(digitalRead(WIND)==LOW && prevRead != LOW){
      prevRead = LOW;
    }    
    delay(2);
  }
  if(count>1){
    rpm = ((float(count)/2)/maxTime)*60; //maxTime is 6 s
    kph = rpm * circ *60;
    
  }else{
    kph=0;
  }
  #ifdef DEBUG
    Serial.println(endMillis);
    Serial.print(" start of getWindSpeed  millis:");
    Serial.println(millis());
  #endif
  //delay(2000);
  rpmWind = int(count/2);  //define rpmWind as global
  return kph;
}


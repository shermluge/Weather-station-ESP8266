# Weather-station-ESP8266
Weather Station with ESP8266 trying for all atmospheric conditions I can get.. For now I have it reading Temp, Humidity, Pressure, Wind speed, Light level. I plan to add Rain measurement, Wind direction, Noise level average. 

This station is up and running for over a month and has given me a lot of good data.

I have it set to Deep Sleep for 5 min, wake up, turn on wind speed ADC, read wind, then read Temps, Humidity, Preasure, and then light level. Then it sends the data to the websight (also adding a second DB at home on a Linux server) to MYSQL. Then it goes back to Deep Sleep.


Schematic is attached here in github but allso available at: https://easyeda.com/shermluge/WX_Station_Full-7d749d96df40475c89a0159e7455dd70


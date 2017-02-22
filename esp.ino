#include <ESP8266WiFi.h>

const char* ssid     = "";
const char* password = "";

// const char* ssid     = "";
// const char* password = "";

const char* host = "ezchx.com";
String inputPuffer;
String url;

const byte numChars = 32;
char receivedChars[numChars];
boolean newData = false;

void setup() {
  
  Serial.begin(9600);
  delay(100);

  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
  }

  delay(5000);

}


void loop() {


  // Retrieve direction from server
  WiFiClient client;
  const int httpPort = 80;
  if (!client.connect(host, httpPort)) {
    Serial.println("connection failed");
    return;
  }
  
  String url = "/carbot/move.php";
  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
               "Host: " + host + "\r\n" + 
               "Connection: close\r\n\r\n");
  delay(3000);

  
  // Send direction to Arduino
  while(client.available()){
    String line = client.readStringUntil('~');
    Serial.print(line.substring(line.length()-1, line.length()));
  }


  // Read sensor data from Arduino
  static byte ndx = 0;
  char endMarker = '\n';
  char rc;

  while (Serial.available() > 0 && newData == false) {
      rc = Serial.read();

      if (rc != endMarker) {
          receivedChars[ndx] = rc;
          ndx++;
          if (ndx >= numChars) {
              ndx = numChars - 1;
          }
      }
      else {
          receivedChars[ndx] = '\0'; // terminate the string
          ndx = 0;
          newData = true;
      }
  }

  inputPuffer = receivedChars;



  // Upload sensor data and set direction command to (P)ause
  if (newData == true) {

    WiFiClient client;
    const int httpPort = 80;
    if (!client.connect(host, httpPort)) {
      Serial.println("connection failed");
      return;
    }

    url = "/carbot/sense.php?readings=" + inputPuffer;
  
    client.print(String("POST ") + url + " HTTP/1.1\r\n" +
             "Host: " + host + "\r\n" + 
             "Connection: close\r\n\r\n");

    inputPuffer = "";
    newData = false;
             
  }



}

// Initialize variables

// Set motor pins and speed
int enableLeft = 9; // left side enable pin
int pinLeft1 = 2; // left side control pin 1
int pinLeft2 = 3; // left side control pin 2
int enableRight = 6; // right side enable pin
int pinRight1 = 4; // right side control pin 1
int pinRight2 = 5; // right side control pin 2
int motorSpeed = 200; // motor speed


// Setup sensor pins
int echoPins[] = {A3, 10, 11, 12, 13, A0, A1, A2};
int dist[8];
int trigPin = 7;
static String inputBuffer;
String sensor_readings;
unsigned long Timer;

void setup() {

  // Configure the motor pin modes
  pinMode(enableLeft, OUTPUT);
  pinMode(pinLeft1, OUTPUT);
  pinMode(pinLeft2, OUTPUT);
  pinMode(enableRight, OUTPUT);
  pinMode(pinRight1, OUTPUT);
  pinMode(pinRight2, OUTPUT);
  
  // Configure the pin modes for the sensors
  for (int thisPin = 0; thisPin < 8; thisPin++) {
    pinMode(echoPins[thisPin], INPUT);
  }
  pinMode(trigPin, OUTPUT);

  // initialize serial communication:
  Serial.begin(9600);
}

void loop() {

  // Read direction from ESP
  if (Serial.available() > 0) {

    inputBuffer = "";
    inputBuffer.concat((char)Serial.read());

    // (I)nitialize
    if (inputBuffer == "I") {
      get_sensor_readings();
    }

    // (S)traight
    if (inputBuffer == "S") {
      go_forward();
      get_sensor_readings();
    }

    // (L)eft
    if (inputBuffer == "L") {
      turn_left();
      go_forward();
      get_sensor_readings();
    }

    // (R)ight
    if (inputBuffer == "R") {
      turn_right();
      go_forward();
      get_sensor_readings();
    }

  }

}


int go_forward() {
  int n_sensor = 0;
  Timer = millis();
  while( ((n_sensor > 12) || (n_sensor <= 0)) && (millis()-Timer < 3000UL) ) {
    analogWrite(enableLeft, motorSpeed);
    analogWrite(enableRight, motorSpeed);
    digitalWrite(pinLeft1, HIGH);
    digitalWrite(pinLeft2, LOW);
    digitalWrite(pinRight1, LOW);
    digitalWrite(pinRight2, HIGH);
    n_sensor = sense(echoPins[0]);
  }
  stop_moving();
}


int turn_left() {
  analogWrite(enableLeft, motorSpeed+55);
  analogWrite(enableRight, motorSpeed+55);
  digitalWrite(pinLeft1, LOW);
  digitalWrite(pinLeft2, HIGH);
  digitalWrite(pinRight1, LOW);
  digitalWrite(pinRight2, HIGH);
  delay(1450);
  stop_moving();
}


int turn_right() {
  analogWrite(enableLeft, motorSpeed+55);
  analogWrite(enableRight, motorSpeed+55);
  digitalWrite(pinLeft1, HIGH);
  digitalWrite(pinLeft2, LOW);
  digitalWrite(pinRight1, HIGH);
  digitalWrite(pinRight2, LOW);
  delay(1450);
  stop_moving();
}


int stop_moving() {
  digitalWrite(pinLeft1, HIGH);
  digitalWrite(pinLeft2, HIGH);
  digitalWrite(pinRight1, HIGH);
  digitalWrite(pinRight2, HIGH);  
}


int get_sensor_readings() {
  sensor_readings = inputBuffer;
  for (int thisPin = 0; thisPin < 8; thisPin++) {
    sensor_readings.concat("~");
    sensor_readings.concat(sense(echoPins[thisPin]));
  }
  Serial.println(sensor_readings);
}


int sense(int ep) {
  int duration, distance;
  digitalWrite(trigPin, LOW);
  delayMicroseconds(5);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  duration = pulseIn(ep, HIGH);
  distance = (duration/2) / 74;
  return distance;
}

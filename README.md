# self_driving_vehicle

![](https://github.com/ezchx/self_driving_vehicle/blob/master/carbot.jpg)

The vehicle used for this project is the SainSmart UNO 4wd car kit plus 8 HC-SR04 sensors plus an ESP8266 Wifi module.

File summary:<br>

o index.php – main program which coordinates the communications between the various programs and devices.<br>
o move.php – move command interface between the vehicle and index.php the MySQL.<br>
o sense.php – sensor reading interface between the vehicle and index.php via MySQL.<br>
o localize.py – Python localization program.<br>
o search.py – Python A* search program.<br>
o esp.ino – ESP8266 sketch which: 1) monitors the remote database for move commands to be sent to the Arduino and 2) receives sensor readings from the Arduino to be sent to the database.<br>
o Sense_and_move.ino – Arduino sketch which collects the sensor data and controls the robot motors.<br>

Index.php starts out by sending an initialize command to the move database. The vehicle reads this information, takes the sensor readings, and sends this information to the sense database. Index.php then runs this information through localize.py to determine the vehicle’s position. Index.php then sends this information to search.py to calculate the best path to the goal and then updates the move database with the next move on that path. The process repeats until the vehicle reaches the goal or falls apart after running into too many obstacles.

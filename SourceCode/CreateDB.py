import mysql.connector

mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="CG47056km",
  database="ANPR",
  allow_local_infile=True
)

mycursor = mydb.cursor()

mycursor.execute("SHOW TABLES")

for x in mycursor:
  mycursor.execute(f"Drop table traffic")

# create table
mycursor.execute("CREATE TABLE traffic (id INT NOT NULL, UniqueID INT NOT NULL, Date DATE NOT NULL, Time TIME NOT NULL, Site INT NOT NULL, Type VARCHAR(50) NOT NULL, Make VARCHAR(50) NOT NULL, Generic_Model VARCHAR(50) NOT NULL, Fuel_Type VARCHAR(50) NOT NULL, Body_Type VARCHAR(50) NOT NULL, co2 INT NOT NULL, Mass INT NOT NULL)")

mycursor.execute("LOAD DATA LOCAL INFILE '/Users/chris/Desktop/Final Year Stuff/FinalYearProject/ProcessedData/ProcessedData.csv' INTO TABLE traffic FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' IGNORE 1 ROWS (id, UniqueID, @Date, Time, Site, Type, Make, Generic_Model, Fuel_Type, Body_Type, co2, Mass) SET Date = STR_TO_DATE(@Date, '%d/%m/%Y')")

mydb.commit()

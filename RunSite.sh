#!/bin/bash

docker-compose up -d
echo Creating site
sleep 30
echo Creating DB
docker exec -it mysqldb bash ./data/CreateDB.sh
echo launching site
open http://localhost:8000/HomePage.php

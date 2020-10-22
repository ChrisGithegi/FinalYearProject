#!/usr/bin/python

import os, sys
import csv

os.chdir("/home/users/sc014212/Documents/FinalYearProject/RawData")
dirs = os.listdir()
#recursively go through raw data and combine into one .csv file
for d in dirs:
	try:
		e = "{}/{}".format(os.getcwd(),d) 
		os.chdir(e)
		try:
			c = os.listdir()
			c2 = []
			with open(c) as csvfile:
				anprData = csv.reader(csvfile)
				print(c)
				for row in anprData:
					c2.append(row)
				print(len(c2))
		except:
			pass
		os.chdir("/home/users/sc014212/Documents/FinalYearProject/RawData")
		
	except:
		pass


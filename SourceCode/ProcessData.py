#!/usr/bin/python3

import os, sys
import csv

os.chdir("../RawData")
dirs = os.listdir()
#recursively go through raw data and combine into one .csv file
writer = csv.writer(open('../ProcessedData/ProcessedData.csv','wt'))
for d in dirs:
	x = 0
	try:
		e = "{}/{}".format(os.getcwd(),d)
		os.chdir(e)
		print(os.getcwd())
		try:
			c = os.listdir()
			c2 = []
			with open(c[0],'rt',encoding='ISO-8859-1') as csvfile:
				anprData = csv.reader(csvfile)
				try:
					if x == 0:
						for row in anprData:
							writer.writerow(row)
					else:
						next(anprData)
						for row in anprData:
							writer.writerow(row)
				except csv.Error as e:
					print(e)
		except Exception as e:
			print(e)
		os.chdir("../")

	except:
		pass
	x+=1

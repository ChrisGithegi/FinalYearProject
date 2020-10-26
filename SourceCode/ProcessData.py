#!/usr/bin/python3
#libraries to be used

import os, sys
import csv
import pandas as pd
import numpy as np
'''
need to get all the data out of all of the CSV files located in different folders
all csv content will be processed and stored in /ProcessedData/ProcessedData.csv
'''
def allData():
	os.chdir("../RawData")
	dirs = os.listdir()
	#recursively go through raw data and combine into one .csv file
	writer = csv.writer(open('../ProcessedData/ProcessedData.csv','wt'))
	x = 0
	for d in dirs:
		#try navigating to each folder to open each csv file to dump the data in a new csv file called ProcessedData.csv
		try:
			e = "{}/{}".format(os.getcwd(),d)
			os.chdir(e)
			print(os.getcwd())
			try:
				c = os.listdir()
				c2 = []
				#open csv file to put info into the processed csv file
				with open(c[0],'rt',encoding='ISO-8859-1') as csvfile:
					anprData = csv.reader(csvfile)
					try:
						#only copy heading from first file.
						if x == 0:
							for row in anprData:
								writer.writerow(row)
							x+=1
						elif x > 0:
							next(anprData)
							for row in anprData:
								writer.writerow(row)
						else:
							continue
					except csv.Error as e:
						print(e)
			#print error if it cannot navigate to desired folder
			except Exception as e:
				print(e)
			os.chdir("../")

		except:
			pass
allData()
'''
now have a total of 814262 records before we cut this down
now get data with the fields needed.
fields needed are: 'UniqueId','Date','Time','site','TYPE','Generic Model','Propulsion Type Desc','BodyTypeDesc2','Mass','Co2'
this will be stored in the same file.
'''
def getTrueData():
	fields = ['UniqueId','Date','Time','site','TYPE','Generic Model','Propulsion Type Desc','BodyTypeDesc2','Mass','Co2']
	df = pd.read_csv('../ProcessedData/ProcessedData.csv',skipinitialspace=True,usecols=fields)
	#replace empty value's with NaN (exception of TYPE field as if not populated, is a private vehicle)
	df['Mass'].replace('',np.nan, inplace = True)
	df['Mass'].replace(0.0,np.nan, inplace = True)
	df['TYPE'].replace(np.nan,'Private Vehicle',inplace=True)
	#replace unknown/missing vehicle titles in the name so that we only have proper car names
	for d in df['Generic Model']:
		f = str(d)
		try:
			if 'Model Missing' in f or 'Unknown' in f or f == '':
				df['Generic Model'].replace(d,np.nan, inplace = True)
			else:
				pass
		except Exception as e:
			pass
	#replace unknown/missing/incorrect vehicle mass' so that we have only accurate vehicle weights.
	for d in df['Mass']:
		try:
			f = int(d)
			if f < 800 or f > 4000:
				df['Mass'].replace(d,np.nan, inplace = True)
			else:
				pass
		except Exception as e:
			if isinstance(d,int):
				pass
			else:
				if d is np.nan:
					pass
				else:
					df['Mass'].replace(d,np.nan, inplace = True)
	#replace unknown/missing/incorrect vehicle Co2 emission values so that we have only accurate figures for Co2 emissions
	for d in df['Co2']:
		try:
			f = int(d)
			if f > 500:
				df['Co2'].replace(d,np.nan, inplace = True)
			else:
				pass
		except Exception as e:
			if isinstance(d,int):
				pass
			else:
				if d is np.nan:
					pass
				else:
					df['Co2'].replace(d,np.nan, inplace = True)
    #drop any fields with null values that are found throughout the csv file.
	df.dropna()
	df['BodyTypeDesc2'].replace('',np.nan, inplace = True)
	df['Co2'].replace('',np.nan, inplace = True)
	df['Co2'].replace(0,np.nan, inplace = True)
	#drop empty fields which have been filled with NAN
	df.dropna(subset=['Mass'], inplace=True)
	df.dropna(subset=['Co2'], inplace=True)
	df.dropna(subset=['Generic Model'], inplace=True)
	df.dropna(subset=['BodyTypeDesc2'], inplace=True)
	df.to_csv('../ProcessedData/ProcessedData.csv')
	print(df)
	print('Written to CSV')
	#resulting in 685936 values ready to use
getTrueData()

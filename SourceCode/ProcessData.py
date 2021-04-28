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
	# recursively go through raw data and combine into one .csv file

	writer = csv.writer(open('../ProcessedData/ProcessedData.csv','wt'))
	x = 0
	for d in dirs:
		# try navigating to each folder to open each csv file to dump the data in a new csv file called ProcessedData.csv

		try:
			e = "{}/{}".format(os.getcwd(),d)
			os.chdir(e)
			print(os.getcwd())
			try:
				c = os.listdir()
				c2 = []
				# open csv file to put info into the processed csv file
				with open(c[0],'rt',encoding='ISO-8859-1') as csvfile:
					anprData = csv.reader(csvfile)
					try:
						# only copy heading from first file.
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
			# print error if it cannot navigate to desired folder
			except Exception as e:
				print(e)
			os.chdir("../")

		except:
			pass
allData()
'''
now have a total of 814262 records before we cut this down
now get data with the fields needed.
fields needed are: 'UniqueId','Date','Time','site','TYPE','Generic Model','Propulsion Type Desc','Body Type Desc 1','BodyTypeDesc2','FirstRegMonth','Mass','Co2'
this will be stored in the same file.
'''
def getTrueData():
	fields = ['UniqueId','Date','Time','site','TYPE','Make Desc','Generic Model','Propulsion Type Desc','FirstRegMonth','Body Type Desc 1','BodyTypeDesc2','Mass','Co2']
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
    # replace unknown/missing Manufacturers
	for d in df['Make Desc']:
		f = str(d)
		try:
			if 'Model Missing' in f or 'Unknown' in f or f == '':
				df['Make Desc'].replace(d,np.nan, inplace = True)
			else:
				pass
		except Exception as e:
			pass
	#replace unknown/missing/incorrect vehicle mass' so that we have only accurate vehicle weights.
	for d in df['Mass']:
		try:
			f = int(d)
			if f < 549 or f > 4000:
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
	df['Body Type Desc 1'].replace('',np.nan, inplace = True)
	df['FirstRegMonth'].replace('',np.nan, inplace = True)

	df['Co2'].replace('',np.nan, inplace = True)
	df['Co2'].replace(0,np.nan, inplace = True)
	#drop empty fields which have been filled with NAN
	df.dropna(subset=['Mass'], inplace=True)
	df.dropna(subset=['Co2'], inplace=True)
	df.dropna(subset=['Generic Model'], inplace=True)
	df.dropna(subset=['FirstRegMonth'], inplace=True)

	df.dropna(subset=['Body Type Desc 1'], inplace=True)
	df.dropna(subset=['BodyTypeDesc2'], inplace=True)
	df.dropna(subset=['Make Desc'], inplace=True)
	df.to_csv('../ProcessedData/ProcessedData.csv')
	print('Written to CSV')
	#resulting in 685936 values ready to use
getTrueData()

def addEuroStd():
	fields = ['UniqueId','Date','Time','site','TYPE','Make Desc','Generic Model','Propulsion Type Desc','FirstRegMonth','Body Type Desc 1','BodyTypeDesc2','Mass','Co2']
	df = pd.read_csv('../ProcessedData/ProcessedData.csv',skipinitialspace=True,usecols=fields)
	conditions = [
	# cars
	(df['FirstRegMonth'] < '1992-12-31')  & (df['Body Type Desc 1'] == 'CARS'),
	(df['FirstRegMonth'] > '1992-12-30') & (df['FirstRegMonth'] < '1997-01-01') & (df['Body Type Desc 1'] == 'CARS'),
	(df['FirstRegMonth'] > '1996-12-31') & (df['FirstRegMonth'] < '2001-01-01') & (df['Body Type Desc 1'] == 'CARS'),
	(df['FirstRegMonth'] > '2000-12-31') & (df['FirstRegMonth'] < '2006-01-01') & (df['Body Type Desc 1'] == 'CARS'),
	(df['FirstRegMonth'] > '2005-12-31') & (df['FirstRegMonth'] < '2011-01-01') & (df['Body Type Desc 1'] == 'CARS'),
	(df['FirstRegMonth'] > '2010-12-31') & (df['FirstRegMonth'] < '2015-09-01') & (df['Body Type Desc 1'] == 'CARS'),
	(df['FirstRegMonth'] > '2015-08-31') & (df['Body Type Desc 1'] == 'CARS'),
	# coaches and buses
	(df['FirstRegMonth'] < '1992-12-31')  & (df['Body Type Desc 1'] == 'BUSES & COACHES'),
	(df['FirstRegMonth'] > '1992-12-30') & (df['FirstRegMonth'] < '1997-01-01') & (df['Body Type Desc 1'] == 'BUSES & COACHES'),
	(df['FirstRegMonth'] > '1996-12-31') & (df['FirstRegMonth'] < '2001-01-01') & (df['Body Type Desc 1'] == 'BUSES & COACHES'),
	(df['FirstRegMonth'] > '2000-12-31') & (df['FirstRegMonth'] < '2006-01-01') & (df['Body Type Desc 1'] == 'BUSES & COACHES'),
	(df['FirstRegMonth'] > '2005-12-31') & (df['FirstRegMonth'] < '2011-01-01') & (df['Body Type Desc 1'] == 'BUSES & COACHES'),
	(df['FirstRegMonth'] > '2010-12-31') & (df['FirstRegMonth'] < '2015-09-01') & (df['Body Type Desc 1'] == 'BUSES & COACHES'),
	(df['FirstRegMonth'] > '2015-08-31') & (df['Body Type Desc 1'] == 'BUSES & COACHES'),
	# taxis
	(df['FirstRegMonth'] < '1992-12-31')  & (df['Body Type Desc 1'] == 'TAXIS'),
	(df['FirstRegMonth'] > '1992-12-30') & (df['FirstRegMonth'] < '1997-01-01') & (df['Body Type Desc 1'] == 'TAXIS'),
	(df['FirstRegMonth'] > '1996-12-31') & (df['FirstRegMonth'] < '2001-01-01') & (df['Body Type Desc 1'] == 'TAXIS'),
	(df['FirstRegMonth'] > '2000-12-31') & (df['FirstRegMonth'] < '2006-01-01') & (df['Body Type Desc 1'] == 'TAXIS'),
	(df['FirstRegMonth'] > '2005-12-31') & (df['FirstRegMonth'] < '2011-01-01') & (df['Body Type Desc 1'] == 'TAXIS'),
	(df['FirstRegMonth'] > '2010-12-31') & (df['FirstRegMonth'] < '2015-09-01') & (df['Body Type Desc 1'] == 'TAXIS'),
	(df['FirstRegMonth'] > '2015-08-31') & (df['Body Type Desc 1'] == 'TAXIS'),
	#Light GOODS <1306kg
	(df['FirstRegMonth'] < '1994-10-01')  & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] < 1306),
	(df['FirstRegMonth'] > '1994-09-30') & (df['FirstRegMonth'] < '1997-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] < 1306),
	(df['FirstRegMonth'] > '1997-09-30') & (df['FirstRegMonth'] < '2001-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] < 1306),
	(df['FirstRegMonth'] > '2000-12-31') & (df['FirstRegMonth'] < '2006-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] < 1306),
	(df['FirstRegMonth'] > '2005-12-31') & (df['FirstRegMonth'] < '2011-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] < 1306),
	(df['FirstRegMonth'] > '2010-12-31') & (df['FirstRegMonth'] < '2015-09-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] < 1306),
	(df['FirstRegMonth'] > '2015-08-31') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] < 1306),
	#Light GOODS 1306 - 1760kg
	(df['FirstRegMonth'] < '1994-10-01')  & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1305) & (df['Mass'] < 1761),
	(df['FirstRegMonth'] > '1994-09-30') & (df['FirstRegMonth'] < '1998-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1305) & (df['Mass'] < 1761),
	(df['FirstRegMonth'] > '1998-09-30') & (df['FirstRegMonth'] < '2002-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1305) & (df['Mass'] < 1761),
	(df['FirstRegMonth'] > '2001-12-31') & (df['FirstRegMonth'] < '2007-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1305) & (df['Mass'] < 1761),
	(df['FirstRegMonth'] > '2006-12-31') & (df['FirstRegMonth'] < '2012-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1305) & (df['Mass'] < 1761),
	(df['FirstRegMonth'] > '2011-12-31') & (df['FirstRegMonth'] < '2016-09-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1305) & (df['Mass'] < 1761),
	(df['FirstRegMonth'] > '2016-08-31') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1305) & (df['Mass'] < 1761),
	#Light GOODS >1760kg - 3500kg
	(df['FirstRegMonth'] < '1994-10-01')  & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1760) & (df['Mass'] < 3501),
	(df['FirstRegMonth'] > '1994-09-30') & (df['FirstRegMonth'] < '1999-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1760) & (df['Mass'] < 3501),
	(df['FirstRegMonth'] > '1999-09-30') & (df['FirstRegMonth'] < '2002-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1760) & (df['Mass'] < 3501),
	(df['FirstRegMonth'] > '2001-12-31') & (df['FirstRegMonth'] < '2007-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1760) & (df['Mass'] < 3501),
	(df['FirstRegMonth'] > '2006-12-31') & (df['FirstRegMonth'] < '2012-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1760) & (df['Mass'] < 3501),
	(df['FirstRegMonth'] > '2011-12-31') & (df['FirstRegMonth'] < '2016-09-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1760) & (df['Mass'] < 3501),
	(df['FirstRegMonth'] > '2016-08-31') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 1760) & (df['Mass'] < 3501),
	#HGV >3500kg - 12000kg
	(df['FirstRegMonth'] < '1992-01-01')  & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 3500) & (df['Mass'] < 12001),
	(df['FirstRegMonth'] > '1991-12-31') & (df['FirstRegMonth'] < '1996-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 3500) & (df['Mass'] < 12001),
	(df['FirstRegMonth'] > '1996-09-30') & (df['FirstRegMonth'] < '2000-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 3500) & (df['Mass'] < 12001),
	(df['FirstRegMonth'] > '1999-12-31') & (df['FirstRegMonth'] < '2005-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 3500) & (df['Mass'] < 12001),
	(df['FirstRegMonth'] > '2005-09-30') & (df['FirstRegMonth'] < '2008-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 3500) & (df['Mass'] < 12001),
	(df['FirstRegMonth'] > '2008-09-30') & (df['FirstRegMonth'] < '2013-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 3500) & (df['Mass'] < 12001),
	(df['FirstRegMonth'] > '2012-12-31') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 3500) & (df['Mass'] < 12001),
	#HGV >12000 kg
	(df['FirstRegMonth'] < '1993-10-01')  & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 12000),
	(df['FirstRegMonth'] > '1993-09-30') & (df['FirstRegMonth'] < '1996-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 12000),
	(df['FirstRegMonth'] > '1996-09-30') & (df['FirstRegMonth'] < '2000-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 12000),
	(df['FirstRegMonth'] > '2000-09-30') & (df['FirstRegMonth'] < '2006-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 12000),
	(df['FirstRegMonth'] > '2006-09-30') & (df['FirstRegMonth'] < '2009-10-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 12000),
	(df['FirstRegMonth'] > '2009-09-30') & (df['FirstRegMonth'] < '2014-01-01') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 12000),
	(df['FirstRegMonth'] > '2013-12-31') & (df['Body Type Desc 1'] == 'GOODS') & (df['Mass'] > 12000),
	#OTHERS
	(df['FirstRegMonth'] < '1992-12-31')  & (df['Body Type Desc 1'] == 'OTHERS'),
	(df['FirstRegMonth'] > '1992-12-30') & (df['FirstRegMonth'] < '1997-01-01') & (df['Body Type Desc 1'] == 'OTHERS'),
	(df['FirstRegMonth'] > '1996-12-31') & (df['FirstRegMonth'] < '2001-01-01') & (df['Body Type Desc 1'] == 'OTHERS'),
	(df['FirstRegMonth'] > '2000-12-31') & (df['FirstRegMonth'] < '2006-01-01') & (df['Body Type Desc 1'] == 'OTHERS'),
	(df['FirstRegMonth'] > '2005-12-31') & (df['FirstRegMonth'] < '2011-01-01') & (df['Body Type Desc 1'] == 'OTHERS'),
	(df['FirstRegMonth'] > '2010-12-31') & (df['FirstRegMonth'] < '2015-09-01') & (df['Body Type Desc 1'] == 'OTHERS'),
	(df['FirstRegMonth'] > '2015-08-31') & (df['Body Type Desc 1'] == 'OTHERS')
	]
	values = ['-', 'Euro 1', 'Euro 2', 'Euro 3', 'Euro 4', 'Euro 5', 'Euro 6']
	values = values*9
	df['Tier'] = np.select(conditions,values)

	df.to_csv('../ProcessedData/ProcessedData.csv')
	print('Written to CSV')

addEuroStd()

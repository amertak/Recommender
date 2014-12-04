import mysql.connector

from mysql.connector import errorcode

cnx = None
cursor = None

def open_connection():
	try:
		global cnx
		cnx = mysql.connector.connect(user = 'cibula', password = 'cibula', host = '192.168.1.100', database = 'recommender_db')

		print("I AM CONNECTED BITCHES")

	except mysql.connector.Error as err:
		if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
			print("Something is wrong with your user name or password")
		elif err.errno == errorcode.ER_BAD_DB_ERROR:
			print("Database does not exists")
		else:
			print(err)
		cnx.close()

def close_connection():
	global cnx, cursor
	if cursor is not None:
		cursor.close()
	if cnx is not None:
		cnx.close()

def query(sql):
	try:
		global cnx, cursor
		if cursor is None:
			cursor = cnx.cursor()

		cursor.execute(sql)
		return cursor
	except mysql.connector.Error as err:
		print(err)
		return {}

def insert(sql):
	try:
		global cnx, cursor
		if cursor is None:
			cursor = cnx.cursor()

		cursor.execute(sql)
		cnx.commit()
		return cursor
	except mysql.connector.Error as err:
		print(err)

	
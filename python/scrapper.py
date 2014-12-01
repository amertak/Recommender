import db
import re
import HTMLParser

working_file = open('working-file.csv', 'a+')
error_file = open('error_file.txt', 'a+')
h = HTMLParser.HTMLParser()
count = 0
db.open_connection()
for (text, id) in db.query('SELECT text, id FROM quotes2'):
	try:
		processed_text = re.sub(r'&lt;.*&gt; ', '', text)
		processed_text = h.unescape(processed_text)
		processed_text = processed_text.lower()
		processed_text = re.sub(r'[^0-9a-zA-Z\s]', '', processed_text)
		processed_text = processed_text.replace('\n', ' ')
		processed_text = processed_text.replace('\r', ' ')
		processed_text = re.sub(r'\s+', ' ', processed_text)

		working_file.write(str(id) + ',' + processed_text + '\n')
	except:
		error_file.write('Error => id:' + str(id))
		count += 1
error_file.write('Total: ' + str(count))
db.close_connection()
working_file.close()
error_file.close()
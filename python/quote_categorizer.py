import db

categorized_quotes = set()
categorized_ids = set()

def get_categories():
	categories_file = open('categories.txt', 'r')
	categories = {}

	for line in categories_file.readlines():
		split_line = line.split(',')
		categories[split_line[0]] = [word.replace('\n', '') for word in split_line[1].split('.')]

	categories_file.close()
	return categories

def categorize_quote(id, quote):
	global categorized_quotes, categorized_ids
	for category,keywords in get_categories().items():
		for word in quote.split(' '):
			if (word in keywords):
				categorized_quotes.add((category,id))
				categorized_ids.add(id)


input_file = open('working-file.csv', 'r')
# output_file = open('categorized_file.csv', 'a+')
lines = input_file.readlines()

for line in lines:
	split_line = line.split(',')
	id = split_line[0]
	quote = split_line[1].replace('\n', '').strip()
	categorize_quote(id, quote)

for line in lines:
	id = line.split(',')[0]
	if id not in categorized_ids:
		categorized_quotes.add(('Ostatni',id))

db.open_connection()
for (category, id) in categorized_quotes:
	db.insert("INSERT INTO categories (category, quote) VALUES (\'" + category + "\', " + str(id) + ");")

db.close_connection()

# for (category,id) in categorized_quotes:
# 	output_file.write(category + ',' + str(id) + '\n')

# output_file.close()
input_file.close()
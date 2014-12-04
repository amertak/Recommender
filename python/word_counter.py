import nltk
import unicodedata
from collections import Counter

def get_stopwords():
	stop_file = open('czechST.txt', 'r')

	tweaked_words = []

	for line in stop_file.readlines():
		line = line.replace('\n', '')
		line = unicode(line, 'utf-8')
		line = unicodedata.normalize('NFKD', line)
		output = ''
		for c in line:
			if not unicodedata.combining(c):
				output += c
		tweaked_words.append(output)

	stop_file.close()
	return set(tweaked_words)

def count_words(lines):
	stopwords = get_stopwords()
	token_list = []
	for line in lines:
		token_list += nltk.word_tokenize(line.split(',')[1])
		filtered_list = [v for v in token_list if v not in stopwords and len(v) > 1]
	count_dictionary = Counter(filtered_list)
	# print count_dictionary
	count_dictionary = count_dictionary.most_common()
	return count_dictionary

input_file = open('working-file.csv', 'r')
count_file = open('wordcount-file.csv', 'a+')
lines = input_file.readlines()

for (word, count) in count_words(lines):
	count_file.write(word + ',' + str(count) + '\n')

input_file.close()
count_file.close()
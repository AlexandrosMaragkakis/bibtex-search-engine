# import the PorterStemmer class
from nltk.stem import PorterStemmer
import re

SPECIAL_CHAR_REGEX = re.compile(
    # detect punctuation characters
    r"(?P<p>(\.+)|(\?+)|(!+)|(:+)|(;+)|"
    # detect special characters
    r"(\(+)|(\)+)|(\}+)|(\{+)|('+)|(-+)|(\[+)|(\]+)|(\,+))")


# create a new PorterStemmer object
stemmer = PorterStemmer()

# define the string to stem
my_string = " 10th International Conference on Modern Circuits and Systems Technologies,\n{MOCAST} 2021, Thessaloniki, Greece, July 5-7, 2021 10th International Conference on Modern Circuits and Systems Technologies,\n{MOCAST} 2021, Thessaloniki, Greece, July 5-7, 2021"

# stem the string
words = my_string.split()
stemmed_string = [stemmer.stem(word) for word in words]
stemmed_string = " ".join(stemmed_string)
stemmed_string = SPECIAL_CHAR_REGEX.sub(' ', string=stemmed_string)


# print the stemmed string
print(stemmed_string)  # "run"

import re
from nltk.tokenize import word_tokenize
from nltk.corpus import stopwords
from nltk.stem import PorterStemmer
from nltk import data
import json
import sys
import bibtexparser
import os
import xml.etree.ElementTree as ET

nltk_data_dir = '../scripts/nltk_data'
data.path.append(nltk_data_dir)

SPECIAL_CHAR_REGEX = re.compile(
    # detect punctuation characters
    r"(?P<p>(\.+)|(\?+)|(!+)|(:+)|(;+)|"
    # detect special characters
    r"(\^+)|(\"+)|(\`+)|(\\+)|(\&+)|(\(+)|(\)+)|(\}+)|(\{+)|('+)|(-+)|(\[+)|(\]+)|(\,+)|((\d+)\b))")


def find_full_name(name, authors):

    try:
        authors = SPECIAL_CHAR_REGEX.sub(' ', string=authors)
        authors = authors.split(' and\n')
        for author in authors:

            if name.lower() in author.lower():

                return re.sub('\s+', ' ', string=author).rstrip().lstrip()
    except:
        # single author
        # ki omws katafera na to petyxw

        return authors
    with open("result.txt", "w") as f:
        f.write("FAILURE")
    exit(1)


def is_indexed(name):

    try:
        name = name.replace(' ', '%20')
    except:
        pass

    solr_server = 'http://solr:8983/solr/new_authors/'
    solr_api_start = 'select?fl=id&indent=true&q.op=AND&q=author%3A%20'
    solr_api_end = '&rows=10&useParams=$wh=json'
    curl_request = "curl -X POST " + solr_server + \
        "'" + solr_api_start + name + solr_api_end + "'"

    response = os.popen(curl_request).read()
    response_dict = json.loads(response)
    try:
        id = response_dict['response']['docs'][0].get('id')
        return id
    except:
        return None


def delete_author(id):

    solr_server = 'http://solr:8983/solr/new_authors/'
    solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary '
    delete_xml = '<delete><query>' + id + '</query></delete>'
    curl_request = "curl -X POST " + solr_server + \
        solr_api + "'" + delete_xml + "'"
    response_xml = os.popen(curl_request).read()
    root = ET.fromstring(response_xml)
    response_header = root.find(".//lst[@name='responseHeader']")
    status = response_header.find(".//int[@name='status']")
    status_value = int(status.text)
    if status_value == 0:
        return 'SUCCESS'
    else:
        return 'FAILURE'


def stem_str(line: str):

    stemmer = PorterStemmer()
    # porter stemmer can't work on multiple words, so we split the line
    words = line.split()

    # create list with stemmed words
    stemmed = [stemmer.stem(word) for word in words]
    stemmed = " ".join(stemmed)
    # remove special chars
    # must replace with ' ' because for example air-to-ground results in airtoground for some reason
    stemmed = SPECIAL_CHAR_REGEX.sub(' ', string=stemmed)

    # remove consecutive spaces that occur after stemming and spaces at the edges
    stemmed = re.sub('\s+', ' ', string=stemmed)
    stemmed = stemmed.rstrip().lstrip()

    text_tokens = word_tokenize(stemmed)
    tokens_without_sw = [
        word for word in text_tokens if not word in stopwords.words()]
    stemmed = ' '.join(tokens_without_sw)

    return stemmed


def debug(text):
    with open('debug.txt', 'w') as f:
        f.write(str(text))


def preprocess_bibtex(filename):

    # TODO [IMPORTANT]:     check if the name appears to the file
    # TODO [NOT IMPORTANT]: field for unique years

    with open(filename) as bibtext_file:
        bib_database = bibtexparser.load(bibtext_file)

    name = sys.argv[2].split('/')[-1].replace('.bib', '')
    name = name.split(' ')
    name = [x.capitalize() for x in name]
    name = ' '.join(name)

    # this condition is used to check if the .bib file was uploaded from the user or downloaded by the python script
    # because the uploaded files (should) only contain the author's last name in the filename
    if '_' in name:
        name = name.split('_')
        name = [x.capitalize() for x in name]
        name = ' '.join(name)
    else:
        name = find_full_name(name, bib_database.entries[0].get('author'))

    # Check if we are in an update document case

    id = is_indexed(name)

    with open('result.txt', 'w') as f:
        f.write("SUCCESS")

    if id:
        status = delete_author(id)
        with open('result.txt', 'w') as f:
            f.write(status)

    output = {'author': name, 'title': '', 'booktitle': '', 'journal': ''}

    # append entries to output keys
    for entry in bib_database.entries:
        output['title'] = output.get('title') + ' ' + entry.get('title')
        if entry.get('ENTRYTYPE') == 'inproceedings' or entry.get('ENTRYTYPE') == 'incollection':
            output['booktitle'] = output.get(
                'booktitle') + ' ' + entry.get('booktitle')
        elif entry.get('ENTRYTYPE') == 'article':
            output['journal'] = output.get(
                'journal') + ' ' + entry.get('journal')

    # preprocessing stage

    output['title'] = stem_str(output.get('title'))
    output['booktitle'] = stem_str(output.get('booktitle'))
    output['journal'] = stem_str(output.get('journal'))

    # write to file as JSON object
    path = "tmp.json"
    with open(path, "w") as f:
        json.dump(output, f)


def preprocess_query(query: list):
    # TODO logic, do not process boolean keywords or field names

    pass


def main():
    if sys.argv[1] == '-bibtex':
        preprocess_bibtex(sys.argv[2])
        # print('tmp.json')
    elif sys.argv[1] == '-query':
        preprocess_query(sys.argv[2:])

    else:
        print('Usage: python3 preprocess.py -bibtex tryfonopoulos.bib')
        print('       python3 preprocess.py -query wireless networks')


main()

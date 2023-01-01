import re
import json
import sys
import os
import xml.etree.ElementTree as ET
from nltk.tokenize import word_tokenize
from nltk.corpus import stopwords
from nltk.stem import PorterStemmer
from nltk import data
import bibtexparser


# Set the path to the NLTK data directory
NLTK_DATA_DIR = '../scripts/nltk_data'
# Add the NLTK data directory to the data path
data.path.append(NLTK_DATA_DIR)

# Compile a regular expression to detect punctuation and special characters
# The regular expression includes the following characters:
# . ? ! : ; + ^ " ` \ & ( ) } { ' - [ ] , (digits)
SPECIAL_CHAR_REGEX = re.compile(
    r"(?P<p>(\.+)|(\?+)|(!+)|(:+)|(;+)|"
    r"(\++)|(\^+)|(\"+)|(\`+)|(\\+)|(\&+)|(\(+)|(\)+)|(\}+)|(\{+)|('+)|(-+)|(\[+)|(\]+)|(\,+)|((\d+)\b))")

# Compile a regular expression to detect punctuation and special characters in queries
# The regular expression includes the following characters:
# ( ) . ? ! : ; + ^ ` \ & } { ' - [ ] ,
SPECIAL_CHAR_REGEX_FOR_QUERIES = re.compile(
    r"(\(+)|(\)+)|(\.+)|(\?+)|(!+)|(:+)|(;+)|(\++)|(\^+)|(\`+)|(\\+)|(\&+)|(\}+)|(\{+)|('+)|(-+)|(\[+)|(\]+)|(\,+)"
)


def find_full_name(name, authors):
    """
    Finds the full name of an author with the given name in a list of authors.

    The function searches for the given name in the list of authors,
    removes punctuation and special characters from the list,
    and returns the full name of the first author that matches the given name.
    If the given name is not found, the function writes "FAILURE" to a result file and exits.

    Args:
        name (str): The name of the author to search for.
        authors (str): A string containing a list of authors separated by ' and\n'.

    Returns:
        str: The full name of the first author that matches the given name.
    """
    try:
        # Remove punctuation and special characters from the list of authors
        authors = SPECIAL_CHAR_REGEX.sub(' ', string=authors)

        # Split the list of authors into individual author names
        authors = authors.split(' and\n')

        # Iterate over the list of authors
        for author in authors:
            # If the given name is found in the current author name, return the full name
            if name.lower() in author.lower():
                # Remove multiple whitespace characters from the author name
                return re.sub(r'\s+', ' ', string=author).rstrip().lstrip()
    except TypeError:
        # If an exception is raised (e.g. in the case of a single author), return the full list of authors
        return authors

    # If the given name is not found in the list of authors, write "FAILURE" to the result file and exit
    with open("result.txt", "w") as f:
        f.write("FAILURE")
    exit(1)


def is_indexed(name):
    """
    Checks if an author with the given name is indexed in a Solr instance.

    The function sends a curl request to the Solr instance to search for an author with the given name,
    and returns the ID of the first matching author if found.
    If no matching author is found, the function returns None.

    Args:
        name (str): The name of the author to search for.

    Returns:
        str: The ID of the first matching author, or None if no matching author is found.
    """
    try:
        # Replace spaces in the name with %20 for the curl request
        name = name.replace(' ', '%20')
    except TypeError:
        pass

    # Set the base URL of the Solr instance and the API endpoints
    solr_server = 'http://solr:8983/solr/final_authors/'
    solr_api_start = 'select?fl=id&indent=true&q.op=AND&q=author%3A%20'
    solr_api_end = '&rows=10&useParams=$wh=json'

    # Construct the curl request
    curl_request = "curl -X POST " + solr_server + \
        "'" + solr_api_start + name + solr_api_end + "'"

    # Send the curl request and read the response
    response = os.popen(curl_request).read()

    # Convert the response to a dictionary
    response_dict = json.loads(response)

    try:
        # Get the ID of the first matching author
        id = response_dict['response']['docs'][0].get('id')

        # Return the ID of the matching author
        return id
    except:
        # If an exception is raised (e.g. if no matching author is found), return None
        return None


def delete_author(id):
    """
    Deletes an author with the given ID from a Solr instance.

    The function sends a curl request to the Solr instance to delete an author with the given ID,
    and returns 'SUCCESS' if the author is successfully deleted or 'FAILURE' if the deletion fails.

    Args:
        id (str): The ID of the author to delete.

    Returns:
        str: 'SUCCESS' if the author is successfully deleted, 'FAILURE' if the deletion fails.
    """
    # Set the base URL of the Solr instance and the API endpoint
    solr_server = 'http://solr:8983/solr/final_authors/'
    solr_api = 'update?commit=true -H "Content-Type: text/xml" --data-binary '
    # Construct the XML payload to delete the author with the given ID
    delete_xml = '<delete><query>' + id + '</query></delete>'
    # Construct the curl request
    curl_request = "curl -X POST " + solr_server + \
        solr_api + "'" + delete_xml + "'"
    # Send the curl request and read the response
    response_xml = os.popen(curl_request).read()
    # Parse the response as XML
    root = ET.fromstring(response_xml)
    # Get the 'responseHeader' element
    response_header = root.find(".//lst[@name='responseHeader']")
    # Get the 'status' element
    status = response_header.find(".//int[@name='status']")
    # Get the value of the 'status' element
    status_value = int(status.text)
    if status_value == 0:
        # If the status is 0, return 'SUCCESS'
        return 'SUCCESS'
    else:
        # If the status is not 0, return 'FAILURE'
        return 'FAILURE'


def stem_str(line: str, type: str):
    """
    Stems, removes special characters, and removes stopwords from a string.

    The function uses the Porter stemmer to stem the words in the string,
    removes special characters and consecutive spaces,
    and removes stopwords if the `type` argument is not 'boolean'.
    The resulting string is returned.

    Args:
        line (str): The input string.
        type (str): The type of the input string. Can be 'bibtex', 'query', or 'boolean'.

    Returns:
        str: The stemmed, sanitized string.
    """
    # Create a Porter stemmer object
    stemmer = PorterStemmer()
    # Split the input string into individual words
    words = line.split()

    # Stem each word in the list
    stemmed = [stemmer.stem(word) for word in words]
    # Join the stemmed words into a single string
    stemmed = " ".join(stemmed)

    # Remove special characters from the string
    if type == 'bibtex':
        # Use the regular expression defined in SPECIAL_CHAR_REGEX to remove special characters
        stemmed = SPECIAL_CHAR_REGEX.sub(' ', string=stemmed)
    else:
        # Use the regular expression defined in SPECIAL_CHAR_REGEX_FOR_QUERIES to remove special characters
        stemmed = SPECIAL_CHAR_REGEX_FOR_QUERIES.sub(' ', string=stemmed)

    # Remove consecutive spaces that occur after stemming and spaces at the edges
    stemmed = re.sub(r'\s+', ' ', string=stemmed)
    stemmed = stemmed.rstrip().lstrip()

    if type != 'boolean':
        # Tokenize the stemmed string and remove stopwords
        text_tokens = word_tokenize(stemmed)
        tokens_without_sw = [
            word for word in text_tokens if not word in stopwords.words()]

        # Join the tokens into a single string
        stemmed = ' '.join(tokens_without_sw)

    return stemmed


def preprocess_bibtex(filename):
    """
    Preprocess a .bib file and write the resulting data to a .json file.

    This function loads a .bib file into a BibDatabase object, gets the name of the author from the filename,
    checks if the author is already indexed in the Solr database, and if so, deletes the existing document.
    It then extracts the titles, booktitles, and journals from the .bib file and stores them in a dictionary.
    The titles, booktitles, and journals are then preprocessed using the stem_str() function and the resulting
    dictionary is written to a .json file.

    Parameters:
    filename (str): The name of the .bib file to be preprocessed.

    Returns:
    None
    """

    # TODO [NOT IMPORTANT]: field for unique years

    # Load the .bib file into a BibDatabase object
    with open(filename) as bibtext_file:
        bib_database = bibtexparser.load(bibtext_file)

    # Get the name of the author from the filename
    name = sys.argv[2].split('/')[-1].replace('.bib', '')
    # Split the name by spaces and capitalize each word
    name = name.split(' ')
    name = [x.capitalize() for x in name]
    name = ' '.join(name)

    # Check if the .bib file was uploaded by the user or downloaded by the script
    # If the file was uploaded by the user, it should contain the author's last name only in the filename
    if '_' in name:
        # If the file was uploaded by the user, split the name by underscores and capitalize each word
        name = name.split('_')
        name = [x.capitalize() for x in name]
        name = ' '.join(name)
    else:
        # If the file was downloaded by the script, find the full name of the author in the .bib file
        name = find_full_name(name, bib_database.entries[0].get('author'))

    # Check if the author is already indexed in the Solr database

    id = is_indexed(name)

    with open('result.txt', 'w') as f:
        f.write("SUCCESS")

    # If the author is indexed, delete their document from the Solr database
    if id:
        status = delete_author(id)
        # Write the result of the delete operation to the 'result.txt' file
        with open('result.txt', 'w') as f:
            f.write(status)

    # Create a dictionary to store the preprocessed data
    output = {'author': name, 'title': '', 'booktitle': '', 'journal': ''}

    # Append the data from each entry in the .bib file to the corresponding key in the output dictionary
    for entry in bib_database.entries:
        # Append the title of the entry to the 'title' key in the output dictionary
        output['title'] = output.get('title') + ' ' + entry.get('title')
        # Check the type of the entry
        if entry.get('ENTRYTYPE') == 'inproceedings' or entry.get('ENTRYTYPE') == 'incollection':
            # If the entry is an 'inproceedings' or 'incollection' type, append the 'booktitle' field to the 'booktitle' key in the output dictionary
            output['booktitle'] = output.get(
                'booktitle') + ' ' + entry.get('booktitle')
        elif entry.get('ENTRYTYPE') == 'article':
            # If the entry is an 'article' type, append the 'journal' field to the 'journal' key in the output dictionary
            output['journal'] = output.get(
                'journal') + ' ' + entry.get('journal')

    # Preprocess the data stored in the output dictionary
    output['title'] = stem_str(output.get('title'), 'bibtex')
    output['booktitle'] = stem_str(output.get('booktitle'), 'bibtex')
    output['journal'] = stem_str(output.get('journal'), 'bibtex')

    # Write the preprocessed data to the 'tmp.json' file as a JSON object
    path = "tmp.json"
    with open(path, "w") as f:
        json.dump(output, f)


def preprocess_normal_query(query: list):
    """
    Preprocesses a normal query (i.e. not a boolean query) by stemming and tokenizing the input,
    and then replacing spaces with %20 to be used in a URL.
    """
    # Check if the input is a single string or a list of strings
    if len(query) > 1:
        # If it's a list, join the strings into a single string
        query = ' '.join(query)
    else:
        # If it's a single string, assign it to the query variable
        query = query[0]

    # Stem and tokenize the query
    stemmed_query = stem_str(query, 'normal query')

    # Replace spaces with %20 for use in a URL
    stemmed_query = stemmed_query.replace(' ', '%20')

    print(stemmed_query)


def preprocess_boolean_query(query: list):
    """
    Preprocesses a boolean search query by joining the list of query terms into a string, stemming the string,
    replacing common boolean operators with their uppercase versions, and replacing spaces with %20.

    Parameters:
        query (list): A list of strings representing the terms in the boolean search query.

    Returns:
        stemmed_query (str): The preprocessed boolean search query.
    """
    # Join the list of query terms into a single string
    query = ' '.join(query)

    # Stem the query string
    stemmed_query = stem_str(query, 'boolean')

    # Replace common boolean operators with their uppercase versions
    stemmed_query = stemmed_query.replace(' or ', ' OR ').replace(
        ' and ', ' AND ').replace(' not ', ' NOT ')

    # Replace spaces with %20
    stemmed_query = stemmed_query.replace(' ', '%20')

    # Print the preprocessed query
    print(stemmed_query)


def main():
    """
    Main function of the preprocessing script. Processes the command line arguments and calls the appropriate
    preprocessing function based on the arguments provided.

    Usage: python3 preprocess.py -bibtex tryfonopoulos.bib
           python3 preprocess.py -query -normal wireless networks
           python3 preprocess.py -query -boolean "wireless networks"
    """
    try:
        # If the first argument is '-bibtex', call the preprocess_bibtex function with the second argument as input
        if sys.argv[1] == '-bibtex':
            preprocess_bibtex(sys.argv[2])

        # If the first argument is '-query' and the second argument is '-normal', call the preprocess_normal_query function with the remaining arguments as input
        elif sys.argv[1] == '-query' and sys.argv[2] == '-normal':
            preprocess_normal_query(sys.argv[3:])

        # If the first argument is '-query' and the second argument is '-boolean', call the preprocess_boolean_query function with the remaining arguments as input
        elif sys.argv[1] == '-query' and sys.argv[2] == '-boolean':
            preprocess_boolean_query(sys.argv[3:])

    # If an IndexError is raised (e.g. if not enough arguments are provided), print "Failure"
    except IndexError:
        print("Failure")


main()

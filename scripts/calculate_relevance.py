import os
import json
import sys
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.metrics.pairwise import cosine_similarity


def retrieve_document(id: str):
    """
    Retrieve a document from the Solr server based on its ID.

    Parameters:
        id (str): The ID of the document to be retrieved.

    Returns:
        dict: A dictionary containing the information of the retrieved document.
    """

    # Set up the base URL and API parameters for the Solr server
    solr_server = 'http://solr:8983/solr/final_authors/'
    solr_api_start = 'select?fl=author%2Ctitle%2Cbooktitle%2Cjournal&indent=true&q.op=OR&q='
    solr_api_end = '&rows=10&useParams=$wh=json'

    # Construct the cURL request for the Solr server
    curl_request = "curl -X POST " + solr_server + \
        "'" + solr_api_start + id + solr_api_end + "'"

    # Execute the cURL request and store the response
    response = os.popen(curl_request).read()

    # Convert the response to a dictionary and return it
    response_dict = json.loads(response)
    return response_dict


def similarity(doc1, doc2):
    """Calculate the cosine similarity between two documents"""

    # Create a CountVectorizer to convert documents into vectors
    vectorizer = CountVectorizer()

    # Convert the documents into vectors
    doc1_vector = vectorizer.fit_transform([doc1]).toarray()
    doc2_vector = vectorizer.transform([doc2]).toarray()

    # Calculate the cosine similarity between the vectors
    similarity = cosine_similarity(doc1_vector, doc2_vector)[0][0]

    print(format(similarity, '.2f'))


def process_doc(doc: dict):
    """
    Concatenate all fields, and get unique words.
        -> Bag-of-words implementation
    Parameters:
    - doc: dict
        The dictionary containing the Solr search results for a given document
    Returns:
    - new_doc: str
        A string representation of the document, 
        with all fields concatenated and all words made unique
    """

    # Extract the title, booktitle, and journal fields from the document
    title = doc['response']['docs'][0]['title'][0]
    booktitle = doc['response']['docs'][0]['booktitle'][0]
    journal = doc['response']['docs'][0]['journal'][0]

    # Concatenate the fields into a single string
    new_doc = title + ' ' + booktitle + ' ' + journal

    # Split the string into a list of words
    new_doc = new_doc.split()

    # Get the unique words in the lists
    distinct_words = set(new_doc)

    # Join the list of unique words back into a single string
    new_doc = " ".join(distinct_words)

    return new_doc


def main():
    """
    Main function to calculate the similarity between two documents.
    Retrieves the documents by their ids and processes them to obtain a bag-of-words representation.
    Then calculates the cosine similarity between the two documents.
    """
    doc1 = retrieve_document(sys.argv[1])  # retrieve the first document
    doc2 = retrieve_document(sys.argv[2])  # retrieve the second document
    doc1 = process_doc(doc1)  # process the first document
    doc2 = process_doc(doc2)  # process the second document
    # calculate the cosine similarity between the two documents
    similarity(doc1, doc2)


main()

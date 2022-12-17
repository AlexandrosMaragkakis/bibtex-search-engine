from sklearn.feature_extraction.text import CountVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import os
import json
import sys


def debug(text, num):
    with open('debug'+num+'.txt', 'w') as f:
        f.write(str(text))


def retrieve_document(id: str):

    solr_server = 'http://solr:8983/solr/final_authors/'
    solr_api_start = 'select?fl=author%2Ctitle%2Cbooktitle%2Cjournal&indent=true&q.op=OR&q='
    solr_api_end = '&rows=10&useParams=$wh=json'
    curl_request = "curl -X POST " + solr_server + \
        "'" + solr_api_start + id + solr_api_end + "'"
    response = os.popen(curl_request).read()
    response_dict = json.loads(response)
    return response_dict


def similarity(doc1, doc2):
    # Create a CountVectorizer to convert documents into vectors
    vectorizer = CountVectorizer()

    # Convert the documents into vectors
    doc1_vector = vectorizer.fit_transform([doc1]).toarray()
    doc2_vector = vectorizer.transform([doc2]).toarray()

    # Calculate the cosine similarity between the vectors
    similarity = cosine_similarity(doc1_vector, doc2_vector)[0][0]

    print(format(similarity, '.2f'))


def process_doc(doc: dict):
    """ Concatinate all fields, and get unique words.
        -> Bag-of-words implementation
    """
    title = doc['response']['docs'][0]['title'][0]
    booktitle = doc['response']['docs'][0]['booktitle'][0]
    journal = doc['response']['docs'][0]['journal'][0]
    new_doc = title + ' ' + booktitle + ' ' + journal
    new_doc = new_doc.split()
    distinct_words = set(new_doc)
    new_doc = " ".join(distinct_words)

    return new_doc


def main():
    doc1 = retrieve_document(sys.argv[1])
    doc2 = retrieve_document(sys.argv[2])
    doc1 = process_doc(doc1)
    doc2 = process_doc(doc2)
    similarity(doc1, doc2)


main()

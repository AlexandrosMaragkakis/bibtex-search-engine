""" data-uop-users.zip 21 files ~1.5minutes"""
# The zipfile module is buildin so we don't need to download the unzip tool
import zipfile
import sys
import glob
import os
import json

files = glob.glob('*.bib')


def unzip(zip_file_path):

    # open the zip file in read-only mode
    with zipfile.ZipFile(zip_file_path, 'r') as zip_file:
        # extract all files from the zip file to the current directory
        zip_file.extractall()


def cleanup():

    bibs = bib_files()
    for file in bibs:
        os.system("rm " + file)
    zips = zip_files()
    for file in zips:
        os.system("rm " + file)


def bib_files():
    # Get a list of all .bib files in the current working directory
    files = glob.glob('*.bib')
    return files


def zip_files():
    files = glob.glob('*.zip')
    return files


def call_preprocess(files):

    path = 'python3 ../scripts/preprocess.py -bibtex '
    for file in files:
        os.system(path+file)
        curl_request = "curl -X POST -H 'Content-Type: application/json' --data-binary @tmp.json http://solr:8983/solr/new_authors/update/json/docs?commit=true"
        # os.popen() is like os.system() but returns output value
        response = os.popen(curl_request).read()
        response_dict = json.loads(response)
        status = response_dict.get('responseHeader').get('status')

        # should be in cleanup()
        os.system("rm tmp.json")

        # response is: unsuccessful
        if status != 0:
            cleanup()
            with open('result.txt', 'w') as f:
                f.write("FAILURE")
                exit()

    # all file uploads to Solr were successful
    with open('result.txt', 'w') as f:
        f.write("SUCCESS")
    cleanup()


def main():

    # usage: python3 mass-import.py <filename>.zip
    unzip(sys.argv[1])
    files = bib_files()
    call_preprocess(files)


main()

import zipfile
import sys
import glob
import os
import json


def unzip(zip_file_path):
    """
    Unzips a zip file and extracts its contents to the current directory.

    Parameters:
        zip_file_path (str): The path to the zip file to be extracted.
    """
    # open the zip file in read-only mode
    with zipfile.ZipFile(zip_file_path, 'r') as zip_file:
        # extract all files from the zip file to the current directory
        zip_file.extractall()


def cleanup():
    """
    This function removes all .bib and .zip files from the current directory.
    """
    bibs = get_bib_files()
    for file in bibs:
        os.system("rm " + file)
    zips = get_zip_files()
    for file in zips:
        os.system("rm " + file)


def get_bib_files():
    """
    Returns a list of all .bib files in the current working directory.

    The function uses the glob module to search for files with the .bib extension
    and returns a list of their names.

    Returns:
        list: A list of strings containing the names of the .bib files.
    """
    # Get a list of all .bib files in the current working directory
    files = glob.glob('*.bib')

    # Return the list of .bib files
    return files


def get_zip_files():
    """
    Returns a list of all .zip files in the current working directory.

    The function uses the glob module to search for files with the .zip extension
    and returns a list of their names.

    Returns:
        list: A list of strings containing the names of the .zip files.
    """
    # Get a list of all .zip files in the current working directory
    # using the glob module
    files = glob.glob('*.zip')

    # Return the list of .zip files
    return files


def call_preprocess(files):
    """
    Calls the preprocess script for each .bib file in the given list and uploads
    the resulting data to a Solr instance.

    The function calls the preprocess script for each file in the list,
    sends the resulting data to a Solr instance using a curl request,
    and checks the status of the request.
    If the status is unsuccessful, the function cleans up and writes "FAILURE" to a result file.
    If all requests are successful, the function writes "SUCCESS" to the result file and cleans up.

    Args:
        files (list): A list of strings containing the names of the .bib files.

    Returns:
        None
    """
    # Set the path to the preprocess script
    path = 'python3 ../scripts/preprocess.py -bibtex '

    # Iterate over the list of .bib files
    for file in files:
        # Call the preprocess script for the current .bib file
        os.system(path+file)

        # Send the resulting data to the Solr instance using a curl request
        curl_request = "curl -X POST -H 'Content-Type: application/json' --data-binary @tmp.json http://solr:8983/solr/final_authors/update/json/docs?commit=true"
        # os.popen() is like os.system() but returns output value
        response = os.popen(curl_request).read()
        response_dict = json.loads(response)
        status = response_dict.get('responseHeader').get('status')

        # Remove the temporary JSON file
        os.system("rm tmp.json")

        # If the request was unsuccessful, clean up and write "FAILURE" to the result file
        if status != 0:
            cleanup()
            with open('result.txt', 'w') as f:
                f.write("FAILURE")
                exit()

    # If all requests were successful, write "SUCCESS" to the result file and clean up
    with open('result.txt', 'w') as f:
        f.write("SUCCESS")
    cleanup()


def main():
    """
    Extracts .bib files from a .zip file, calls the preprocess script for each .bib file, and uploads the resulting data to a Solr instance.

    The function extracts .bib files from a .zip file,
    calls the preprocess script for each .bib file,
    and sends the resulting data to a Solr instance.

    Args:
        None

    Returns:
        None
    """
    # Extract the .zip file passed as a command-line argument
    # usage: python3 mass-import.py <filename>.zip
    unzip(sys.argv[1])

    # Get a list of .bib files in the current working directory
    files = get_bib_files()

    # Call the preprocess script for each .bib file and upload the resulting data to a Solr instance
    call_preprocess(files)


main()

import sys
import requests
import bs4


def find_id(name):
    """
    Finds the id of an author by searching their name on DBLP.
    """

    # Search for the name on DBLP
    url = f"https://dblp.org/search/author/api?q={name}&h=1000&format=xml"

    # Send a request to the URL and retrieve the response
    response = requests.get(url)

    # Parse the response as XML
    soup = bs4.BeautifulSoup(response.text, "xml")

    # Extract the url of the first result
    result = soup.find("url")
    url_with_id = result.text

    # Extract the id from the url
    id = url_with_id.split(sep='/')
    id = '/'.join(id[-2:])

    # Return the id if it was found
    if id:
        return id

    # Return None if the name was not found
    return None


def download_bibtex(id, name):
    """
    Download the BibTeX file from DBLP given the author's ID and name.
    Save the file to a file named after the name.
    """
    # Download the BibTeX file from DBLP
    url = f"https://dblp.org/pid/{id}.bib"
    response = requests.get(url)

    # Save the BibTeX file to a file named after the name
    filename = name.split(' ')
    filename = '_'.join(filename)
    path = '../scripts/'

    with open(f"{path}{filename}.bib", "w") as file:
        file.write(response.text)

    return f"{path}{filename}.bib"


def main():
    """
    Main function for the get-bibtex script.
    Check if a name was provided as an argument, and if so, find the ID of the name on DBLP and download the BibTeX file.
    """
    # Check if a name was provided as an argument
    if len(sys.argv) < 2:
        print("Please provide a name as an argument")
        print("example: python3 get-bibtex.py costas vassilakis")
        return

    # Find the ID of the name on DBLP
    name = sys.argv[1:]
    name = ' '.join(name)
    id = find_id(name)
    if id is None:
        print(f"Could not find ID for {name}")
    else:
        output = download_bibtex(id, name)
        print(output)


main()

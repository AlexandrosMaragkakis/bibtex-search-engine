import requests
import bs4
import sys
# Function to find the ID of a name on DBLP


def find_id(name):
    # Search for the name on DBLP
    #url = f"https://dblp.org/search?q={name}"
    url = f"https://dblp.org/search/author/api?q={name}&h=1000&format=xml"

    response = requests.get(url)
    soup = bs4.BeautifulSoup(response.text, "xml")

    result = soup.find("url")
    url_with_id = result.text
    id = url_with_id.split(sep='/')
    id = '/'.join(id[-2:])

    if id:
        return id

    # Return None if the name was not found
    return None

# Function to download the BibTeX file for a given ID


def download_bibtex(id, name):
    # Download the BibTeX file from DBLP
    url = f"https://dblp.org/pid/{id}.bib"
    response = requests.get(url)
    #lastname = name.split(' ')[-1]

    # Save the BibTeX file to a file named after the name
    filename = name.split(' ')
    filename = '_'.join(filename)
    path = '../scripts/'

    with open(f"{path}{filename}.bib", "w") as file:
        file.write(response.text)

    return f"{path}{filename}.bib"
# Main function


def main():
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

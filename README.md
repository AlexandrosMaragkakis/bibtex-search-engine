# ASTERAS (Automated Search for TExt and ReseArcherS)

## Description

This repository contains the source code for a search engine web application developed for an Information Retrieval course project at a university. The application is built using Docker and implements a search engine for BibTeX files containing information about researchers. In addition to indexing, deleting, and searching, the application also includes a tool that calculates the relevance of indexed authors. The project has been analyzed using SonarCloud to ensure high code quality and maintainability standards.

The application is implemented using HTML, CSS, JavaScript, jQuery, Ajax, Python, Apache Solr, and various Python libraries including requests, bs4, bibtexparser, nltk, lxml, and scikit-learn. BibTeX files can be uploaded individually or as a zip file, and the application provides an option to search for a researcher's BibTeX file by scraping the DBLP website using the provided name.

Installation and usage instructions can be found in this readme file.

We would like to acknowledge our professors Costas Vassilakis and Christos Tryfonopoulos for their assistance in the development and utilization of Docker in this project.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Dependencies

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

### Installing

1. Clone the repository:

   ```
   git clone https://github.com/AlexandrosMaragkakis/bibtex-search-engine
   ```

2. Adjust file permissions

   **_Windows users:_**

   You may need to enter the following commands using `git bash`

   **_Linux and macOS users:_**

   If you are using Linux or macOS, you need to adjust the file permissions of the project directory after cloning the repository. To do this, run the following command:

   ```
   sudo chmod -R 777 [project-directory]
   ```

3. Navigate to the project directory:

   ```
   cd [repository-name]
   ```

### Executing program

**_Linux and macOS users:_**

- Build the Docker images and start the containers (this may take several minutes the first time you run the app):

  ```
  docker compose up -d
  ```

### Stopping the containers

- To stop the containers, enter
  ```
  docker compose down
  ```

**_Windows users:_**

- Use Docker Desktop

The Python libraries and Python interpreter will be installed and configured automatically inside the web application container.

## Authors

- [Alexandros Maragkakis](https://github.com/AlexandrosMaragkakis)
- Zamir Osmenaj

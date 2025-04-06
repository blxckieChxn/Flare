from urllib.request import urlopen, urlretrieve
from urllib.error import HTTPError
from html.parser import HTMLParser
from tqdm import tqdm
import re

def getEditions(url):

    response = urlopen(url)
    html = response.read().decode('utf-8')
    small_tags = re.findall(r'<small.*?>(.*?)</small>', html, re.DOTALL)
    editions = [tag.strip() for tag in small_tags]
     
    return editions

def main():
    url = "https://scryfall.com/sets"
    editions = getEditions(url)
    print(editions)
    num = len(editions)
    print(num)
    return None

if __name__ == "__main__":
    main()
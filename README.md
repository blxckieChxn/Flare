# Flare
Build your own Magic The Gathering database filtering through editions, card numbers and/or names.

scryscrap.py is intended to help you make a starting point from where you can build this database. It iterates through a scryfall URL,
for each, it downloads the card image with its name and saves it in the "imagenes" folder. The default edition is Comander Masters (cmm in the URL), you can change it looking for abbreviation 
codes depending on the edition you wanna download.

sqlinput.py is the second step, it will help with creating a table in a database (change the default values in the code) and then add every image from a folder into it.

scryPriceUpdater.py is the third step. You will need to have a 'price' atribute defined in your database. This runs through all editions, all names and their respective serial number from your own database, then it'll make a request for each of them to the Scryfall web and upload the result right after. The python code gets only NON FOIL card prices and it is obtained in EUR and USD.

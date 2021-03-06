# meatloaf-svr

[![discord chat](docs/discord.sm.png)](https://discord.gg/FwJUe8kQpS)

Server for hosting software via HTTP for use with [Meatloaf](https://github.com/idolpx/meatloaf).<br/>
A Commodore 64/128/VIC20/+4 multi-device emulator.<br/>

![LOAD_HTTP](docs/meatloaf64-svr.png)

## INSTALLATION

* Copy the "api" folder to your PHP enabled webserver
* Edit the settings in "api/config.php"
* On your C64  with Meatloaf attached enter:
  
  ``` CBMDOS
  LOAD"HTTP://YOURDOMAIN.COM",8 
  ```

## TODO

* Add features for configuring Meatloaf device via server
* Add Search function
* Add Randomizer
* Add GOTD features
* Add High Score features
* Add [CSDB Web Service](https://csdb.dk/webservice/) Support for searching

## References

* [VICE Emulator File Formats](https://vice-emu.sourceforge.io/vice_17.html#SEC329)
* [C64 Formats Documents](https://ist.uwaterloo.ca/~schepers/formats.html)
* [Easy File System (EasyFS)](https://skoe.de/easyflash/files/devdocs/EasyFlash-AppSupport.pdf)
* [BackBit D8B Disk Format (p.14)](https://backbit.io/downloads/Docs/BackBit%20Cartridge%20Documentation.pdf?page=14)
* [TapeCart TCRT File Format](https://github.com/ikorb/tapecart/blob/master/doc/TCRT%20Format.md)
* [Power64 Emulator File Formats](https://www.infinite-loop.at/Power64/Documentation/Power64-ReadMe/AE-File_Formats.html)
* [Commodore 64 Tape Info Central](https://www.geocities.ws/SiliconValley/Platform/8224/c64tape/index.html)
  
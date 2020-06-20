# SimpNAS
Manage your hard drives, volumes, network shares, users, groups through an excellent fast Web UI similar to OpenMediaVault, UnRAID and FreeNAS, but with a much more simpler approach.

## Requirements
* At least two Hard Drives: one for the OS and the other drives for storage.

## Installation

* Install Debian 10
* Copy and paste the line below into terminal

`wget https://raw.githubusercontent.com/johnnyq/simpnas/master/install.sh; bash install.sh`

## Notes

* When installing Debian for packages choose only SSH server and standard system utilities.
* Active Directory requires that your network card is set statically.
* This is meant to be installed behind a router not exposed to the internet directly.
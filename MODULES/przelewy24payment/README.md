Instrukcja budowania frontu w module przelewy24payment:
Moduł od strony frontu podzielony jest na dwie części (katalogi):
_admin-dev - odpowiada za style oraz logikę JS w BackOffice Prestashop
_theme-dev - odpowiada za style oraz logikę JS w FrontOffice Prestashop
Aby zbudować front, należy:
Wejść do pożądanego katalogu (_theme-dev / _admin-dev)
Upewnić się, że wersja node jest odpowiednia:
przy wykorzystaniu NVM'a wystarczy komenda nvm use
bez wykorzystania NVM'a - w pliku .nvmrc podana jest odpowiednia wersję node'a dla bezproblemowego zbudowania zależności
Zainstalować node-modules: npm install
Odpalić pożądany skrypt:
npm run watch - służy do aktywnego developmentu, wykrywa zmiany w plikach źródłowych i buduje po wykryciu zmian
npm run build - po etapie aktywnego developmentu należy zbudować assety do wersji produkcyjnej i takie stosować w paczce wydawniczej

Aby zbudowac zip należy odpalic:

make -f {sciezka do pliku Makefile} -C {sciezka do root modulu} build-zip

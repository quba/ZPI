vp/README
=========

Ten folder zawiera wszelkie pliki związane z projektem systemu konferencyjnego
CoMaS.
W niniejszym pliku znajduje się krótki opis artefaktów zawartych w pliku
projektu Visual Paradigm: CoMaS.vpp.

***Wizja***
-----------

Plik ZPI - wizja 1.1.pdf zawiera opis tworzonego produktu. Został dostarczony
przez naszego Product Ownera i nawet nie śmiałem wprowadzać do niego
jakiekolwiek zmiany. Pozwolę sobie jednak zakwestionować słuszność niektórych
pomysłów. Np. nie rozumiem trochę roli Konfiguratora, który został dodany
trochę na siłę. Jak na mój gust jeśli już miałby się znaleźć w projekcie to
byłby to po prostu Moderator - aktor z podobnymi uprawnieniami do
Administratora, który ma wpływ jedynie na początkową konfigurację, zarządzanie
kontami użytkowników. To co odnosi się do zarządzania konferencjami powinien
robić Organizator, gdyż on jest twórcą danej konferencji i jest za nią
odpowiedzialny, a Administrator, czy Moderator powienien być jedynie
odpowiedzialny za poprawne funkcjonowanie serwisu.

***Wymagania funkcjonalne***
----------------------------

Wymagania funkcjonalne zamieściłem w sześciu sekcjach. Każda z sekcji dotyczy
innego użytkownika. Niektóre wymagania zamieszczone są w nawiasach co oznacza,
że są opcjonalne, alebo że nie jestem pewien czy powinny znaleźć pokrycie w
projekcie. Tworząc ten dokument kierowałem się przede wszystkim rozdziałem "Opis
systemu" z Wizji.

***Diagram przypadków użycia***
-------------------------------

Tworząc przypadki użycia starałem się w jak największym stopniu pokryć
wymagania funkcjonalne określone w pliku Wymagania funcjonalne.pdf.
Pogrupowałem Use Cases kolorami według tego jakich pojęć w największej mierze
dotyczą. Np. wszystko co związane z paper jest szare, to co związane z
rejestracją jest czerwone, to co z tworzeniem i zarządzaniem konferencjami jest
pomarańczowe, to co związane z autoryzacją jest zielone, to co związane z
zarządzaniem kontami użytkowników jest błękitne, a to co związane z recenzjami
jest fioletowe.
Generalnie to co robią editor i tech. editor to jest to samo,
tyle że różni się nazwą, dlatego zrobiłem abstract editora po którym dziedziczą
po to żeby nie wprowadzać nadmiarowych kopii UC. To co jest używane do więcej
niż jednego UC includuję, zamiast używać extend. Gdybym używał extend to byłby
problem z oddzieleniem tych rozszerzeń które może używać np. Organizer a
których nie może używać User.

***Domenowy diagram klas***
---------------------------

W nowej wersji wróciłem do starej koncepcji relacji Paper-Registration ale
tym razem z innymi licznościami. Rejestracja na konferencję powinna już śmigać.
Dodałem również relację User-Paper gdyż potrzebna będzie przy przydzielaniu
Edytora do Pracy. Pozmieniałem też kierunki kilku relacji oraz pododawałem
kilka nowych pól. Ten diagram może jeszcze ulegać drobnym zmianom.

***Diagram ORM***
-----------------

Diagram ORM wyleciał z projektu. Zastąpiony został przez diagram encji.

***Diagram encji***
-------------------

Jest to już finalna wersja diagramu encji. Encje bezpośrednio pokazują nam jak wyglądać będzie nasza baza danych i na podstawie nich tworzone są klasy encji mapowalnych przez Doctrine. Ten diagram meże jeszcze ulegać drobnym zmianom.



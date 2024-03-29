Διαχείριση Προγραμμάτων Σχολικών Δραστηριοτήτων
===================

Βαγγέλης Ζαχαριουδάκης, ΠΕ86
Πληροφορίες: sugarv@sch.gr
(c) 2024

Το σύστημα Διαχείρισης Σχολικών Δραστηριοτήτων αποτελεί ένα εύκολο & ολοκληρωμένο τρόπο υποβολής, ελέγχου, επικαιροποίησης και παρακολούθησης προγραμμάτων σχολικών δραστηριοτήτων Δ/νσης Εκπαίδευσης.
Με τη χρήση του, 
- εκλείπει η ανάγκη συνεχούς αλληλογραφίας με την υπηρεσία, 
- δίνεται η δυνατότητα υποβολής/διόρθωσης όλο το 24ωρο.

Επιπλέον, επέχει θέση υπεύθυνης δήλωσης από το Δ/ντή - Πρ/νο της σχολικής μονάδας, διότι η είσοδος σε αυτό γίνεται μέσω του επίσημου λογαριασμού της μονάδας στο Πανελλήνιο Σχολικό Δίκτυο (www.sch.gr).

Αναπτύσσεται εξ'ολοκλήρου με εργαλεία ανοιχτού λογισμικού (βλ. παρακάτω).


Οδηγίες
--------
1. Δημιουργία των πίνακων με βάση τα sql αρχείο που βρίσκονται στο φάκελο files (αφού γίνει έλεγχος & τροποποίηση των πεδίων τύπου enum).
2. Τροποποίηση του conf.php.
3. Εκτέλεση του [composer](https://getcomposer.org/) για εγκατάσταση των απαραίτητων βιβλιοθηκών.
4. Συνεννόηση με τους διαχειριστές του ΠΣΔ για χρήση της υπηρεσίας CAS με την εφαρμογή
5. Ανέβασμα αρχείων και έναρξη λειτουργίας.


Εργαλεία που χρησιμοποιήθηκαν / Tools used:
---------------------------
- Bootstrap (https://getbootstrap.com/)
- PHPWord (https://github.com/PHPOffice/PHPWord)
- phpCAS (https://github.com/Jasig/phpCAS)
- jQuery (https://jquery.com/)
- MySQL (https://www.mysql.com/)


----------

School program applications management
====================

An easy way to insert, update and watch school program applications.

Authenticates schools via sch.gr's CAS server and gives the ability to submit, edit and export school program applications 24/7, eliminating the need for constant communication with the administration.

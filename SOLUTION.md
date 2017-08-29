SOLUTION
========

Estimation
----------
Estimated: 8 hours

Spent: 10 hours


Solution
--------

The solution has been upgraded, so that user can see all year separetly. In addition he can choose which year he wants to see and if he want name sorted descending. 


Technical Setup
===============

In Vagrant the following technologies are used: PHP 7, MySQL 5.7 and Ubuntu 16.04.

1. On the command prompt, run:

    $> cd /path/to/the/test/folder
    
    $> vagrant up (this will provision database and install composer)

    $> vagrant ssh 

2. The code in test is mirrored to /vagrant inside virtual enviroment. If you test on Windows machine, then check that console file has Unix line endings, otherwise it will fail.

3. Test everything.

How to make it better:
======================

	- allow user to chose which year does he want to see (done for single year (1h)), should allow multiple years (2h)
	- allow user to sort names DESC (done, 1h)
	- allow user to export to csv or excel (4h)
	- allow user to chose which months he want to see (2h)
	- plot some data in histogram (2h)


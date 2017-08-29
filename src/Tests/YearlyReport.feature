1. If there is no historical data, the yearly report should print out: "There is no data in views table." and exit.

2. If there is a historical data, the yearly report should generate n+1 tables, where n presents the number of the unique years inside views table. The +1 represents sumed view, which groups only by month and sums all years.

3. If there is a historical data, but one year in between is missing, the report for this year, should print out: "There is no data for this year" 

4. If there is a historical data but a year does not have some months present in historical data, the string "n/a" should be present instead (not all of them should be n/a!). 

5. If there is a historical data but a user does not have any of the data, it should not be shown in the results.

Bonus tests:

6. if a user does insert year when prompted, the result should print 2 tables, one for exact year and other for all years.

7. if a user writes DESC when promped for ordering, the names in results should be printed in reverse alphabetical order
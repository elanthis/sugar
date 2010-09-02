Test: loop $i in 1,3 ; $i ; /loop
Expect: 123
Result: {{ loop $i in 1,3 ; $i ; /loop }}

Test: loop $i in 3,1,-1 ; $i ; /loop
Expect: 321
Result: {{ loop $i in 3,1,-1 ; $i ; /loop }}

Test: loop $i in 3,4*6,7 ; $i ; /loop
Expect: 3101724
Result: {{ loop $i in 3,4*6,7 ; $i ; /loop }}

Test: loop $i in 2,1 ; $i ; /loop 
Expect: 
Result: {{ loop $i in 2,1 ; $i ; /loop }}

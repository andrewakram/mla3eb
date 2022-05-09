//mla3eb
==========================
//download the project
1- git clone https://github.com/andrewakram/mla3eb.git

//config for project and DB 
2- create .env file

//download composer (vendor files)
3- composer update

//run migration of DB with seeders
4- php artisan migrate:fresh --seed

//postman link for APIs
5- https://www.getpostman.com/collections/1960decd5e91df8f2b5b

//api for get pitches with available slots
--GET
--http://127.0.0.1:8000/api/V1/app/get-pitch-slots?date=2022-01-01
--parameters
>date:2022-01-01

//api for book order
--POST
--http://127.0.0.1:8000/api/V1/app/book-order
--body parameters
>pitche_id:1
>date:2024-01-01
>time:09:30

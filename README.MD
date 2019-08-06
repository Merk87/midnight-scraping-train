# PROHIBITION PARTNERS TEST

###### Author: Viktor Moreno Alhambra (AKA Merkury)

## The problem to solve.
We need to create a small console application that can be run with the normal LAMP stack 
(I've develop all the solution directly running on docker).
This application when executed will gather news from a specific web site.

# Why, how, whaaaaaat?

To develop the proposed test I decided to use a simple Symfony approach.

I decided started to approach the solution of this problem, by designing the database. The exercise actually outlines very well how the entities should be structure. Once 
I was happy with my entities I've moved to design the system.

For this I decided to take the route of the Clean Code,
I tried to keep the code in each function to the bare minimum, with a clear function and variable
naming, so it's easy to read.
For the solution I decided to abstract as much as possible, for this I've defined an 
AbstractScraper, Scraper interface and the web scraper, that the late is being use by our
command to execute the task at hand.

For the test, as the exercise determine to don't use more than 2 or 3 hours, I decided to add a 
minimum unit test, as setting all the dev environment, etc it would have consume a big part of the 
time I had for the test. Even the test are at the minimum and knowing that 
this would be *totally unacceptable* in a real environment, I think at least can help you grasp 
my understanding of them.

# Improvements and ideas never implemented.
* Caching (really, really, wanted to, but it's late)
* Automatic following of the pages in the home to keep scrapping recursively.
* A mechanism to detect urls and select the appropriated Scraper
* More testing.
* Creation of a quick command to consult the data that is on storage

# Requirements
* PHP >= 7.2
* Composer
   
To run the project, first run `composer install` to get all the vendor bundles and proceed to 
add the database configuration and the run `php bin/console d:d:create && php bin/console d:s:c`

# Usage
When the database is installed and the project is totally configured you can test it with:
`php bin\console app:scrap-articles <url>`

To run the unit tests, you should run: `php bin/phpunit
`
# Final considerations
As part of my core values I believe that one can not know everything. So for me the only
way to improve is open to the world what I can do and present it as humbly as possible, so please, 
I would *love* to get feedback, positive or negative (I'm a big boy, I can take both!) 
so this test helps me to grow even a bit more.

I hope you find this solution as satisfactory as I've found to solve it and we can have a chat soon.

Thanks.

Victor Moreno Alhambra
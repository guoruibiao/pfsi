# PFSI


## What is it?
PHP Function Set Installer。

An easy tool for downloading the common functions during developing by using PHP.

## How to use?

- 1. Just copy the `pfsi.php` to the path you want to set your common functions.
- 2. writing the `dependency.xml`, the common style like this.
 ```
 <?xml version="1.0" encoding="UTF-8" ?>
 <dependencies>
     <!--比如使用一个关于数据库的轮子-->
     <dependency>
         <name>db</name>
         <srcPath>/cloud/db.php</srcPath>
         <savedPath>/commons/db.php</savedPath>
     </dependency>
 
     <!--要引入的函数详情-->
     <dependency>
         <!--函数名称，应该保证是唯一的-->
         <name>test1</name>
         <!--函数集保存在服务器上的位置-->
         <srcPath>/cloud/test1.php</srcPath>
         <!--函数集将要保存到本地项目中的位置-->
         <savedPath>/commons/test1.php</savedPath>
     </dependency>
 
 ```
- 3. check it out by comparing with the `dependency.xml` which on the github repository.
more details, watch [me](https://github.com/guoruibiao/pfsi).

## How to contribbute?

While I am very happy to see the code writing by you guys.

But it's good to understand that we need some rules, which can be listed as follows.

- 1. Obey the `style` in the file named `dependency.xml`.
- 2. You should test you code very carefully to make sure it can works well.
- 3. Write comments in details.


---


Lastly, welcome to join us.


> The world will belong to those who create something scarce, not something cheap.


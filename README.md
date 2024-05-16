<a name="readme-top"></a>

<!-- PROJECT SHIELDS -->
<!--
*** I'm using markdown "reference style" links for readability.
*** Reference links are enclosed in brackets [ ] instead of parentheses ( ).
*** See the bottom of this document for the declaration of the reference variables
*** for contributors-url, forks-url, etc. This is an optional, concise syntax you may use.
*** https://www.markdownguide.org/basic-syntax/#reference-style-links
-->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]



<!-- PROJECT LOGO -->
<br />
<div align="center">
  <h3 align="center">API File Cloud</h3>

  <p align="center">
    The REST API of the cloud file storage service
    <br />
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#the-main-functionality">The Main Functionality</a></li>
      </ul>
    </li>
    <li>
      <a href="#requirements">Requirements</a>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
    </li>
    <li><a href="#documentation">Documentation</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

The REST API of the cloud file storage service. Written using the Yii2 framework.

### The Main Functionality

For an unauthorized user:
  * Authorization
  * Registration

For an authorized user:
  * The ability to reset authorization
  * Working with files
    * Download
    * Editing
    * Removal
  * Differentiation of file access rights

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- REQUIREMENTS -->
## Requirements

The minimum requirement by this project template that your Web server supports PHP 7.4.



<!-- GETTING STARTED -->
## Getting Started

To get a local copy up and running follow these simple example steps.

1. Clone the repo
   
   ```sh
   git clone https://github.com/Semenov-Daniil/api-file-cloud.git
   ```
2. Edit the file `config/db.php` with real data, for example:
   
    ```php
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=yii2basic',
        'username' => 'root',
        'password' => '1234',
        'charset' => 'utf8',
    ];
    ```
3. Make a database migration
   
   ```sh
   php yii migrate
   ```


You can then access the application through the following URL:

~~~
http://localhost/api-file-cloud/api-file
~~~

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- DOCUMENTATION -->
## Documentation

Postman documentation is provided for this API [Documentation](https://documenter.getpostman.com/view/27801909/2sA3JRaKsL)_

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE.txt` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- CONTACT -->
## Contact

Semenov Daniil - ds.daniilsemen.ds@gmail.com

Project Link: [https://github.com/Semenov-Daniil/api-file-cloud](https://github.com/Semenov-Daniil/api-file-cloud)

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

* [Yii2](https://www.yiiframework.com/doc/guide/2.0/ru)

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/Semenov-Daniil/api-file-cloud.svg?style=for-the-badge
[contributors-url]: https://github.com/Semenov-Daniil/api-file-cloud/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/Semenov-Daniil/api-file-cloud.svg?style=for-the-badge
[forks-url]: https://github.com/Semenov-Daniil/api-file-cloud/network/members
[stars-shield]: https://img.shields.io/github/stars/Semenov-Daniil/api-file-cloud.svg?style=for-the-badge
[stars-url]: https://github.com/Semenov-Daniil/api-file-cloud/stargazers
[issues-shield]: https://img.shields.io/github/issues/Semenov-Daniil/api-file-cloud.svg?style=for-the-badge
[issues-url]: https://github.com/Semenov-Daniil/api-file-cloud/issues
[license-shield]: https://img.shields.io/github/license/Semenov-Daniil/api-file-cloud.svg?style=for-the-badge
[license-url]: https://github.com/Semenov-Daniil/api-file-cloud/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/othneildrew
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com 

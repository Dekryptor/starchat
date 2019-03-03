# Starchat - Chat together

[![Build Status](https://travis-ci.org/nekobit1/starchat.svg?branch=master)](https://travis-ci.org/nekobit1/starchat)

Starchat has a specific goal, to make communication easy again. Starchat is an open source chat program that is hosted on a web server, offering an easy api that lets developers create applications.

Starchat is powered by PHP and accepts http requests. Only your username and password is required to create an account!

Starchat works like any other chat program, but if you write a client it would be like email, as you would add together you're favorite servers, and then it'll treat it like one large server, or you can use the web client, like bookmark them to each page

Head over to the [Wiki's](https://github.com/nekobit1/starchat/wiki) for how to use the API

# Features
Starchat has many cool features that I am working on and experimenting with, to name a few

- Send Message Support
- Voice/Video call with Jit.si Support (In an iframe, works and is AWESOME!)
- API
- Modern Web interface
- Easy to setup

# Installation
Starchat was made to be flexible in a way that lets you just drop in the files and then you are done, all setup is done server side.

Run `chown apache:apache starchat -R` to give apache access for the setup script, replace `apache` with `www-data` if that doesnt work, like on debian.

Then run `setup.php` in your web browser and simply accept the options like normal, once done it is safe to delete setup.php and the install is now complete! Make any custom modifications if you would like.

# Can I talk to others on different servers?
No, originally you could, but it encountered many problems, starchat was supposed to be secure, but being this open for a simple feature causes too many security issues, in the future this may change as requested.

# Is starchat going to have an official server?
Probably, the idea is that you can create your own starchat hosting service, and offer free accounts that others can use, depending on if your server is beefy enough to host it.

# What's that license?
The license is the MIT license. View LICENSE for details

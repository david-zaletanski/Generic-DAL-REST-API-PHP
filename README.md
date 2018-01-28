# Database Query REST API

**Updated README:** 1/28/18

At one point in time I set out to create a very generalized personal data tracking system, mostly for my own uses. I wanted to be able to track anything - calories, exercise, money, etc. This project, called Trackula and then Trackmini, turned out to be overly ambitious and didn't make it very far... There's a surprising amount of design and docs in there tlaking about the project but most can be safely ignored (really not missing much).

I uploaded this because the interesting part of this project (err the only part) is the **Generic PHP driven REST API** that can used to perform CRUD operations on any database table. Could come in handy some day down the road for a simple shared data store.

# Trackmini

Trackmini was the lightweight brother of the Trackula project. The goal of this project was to create a platform in which a user can gather data and track anything and everything about their lives. **Trackmini**, however, can only tracks a subset of ... everything.

**Note:** This project never really made it past the database REST API, and even that had a couple minor bugs to figure out if I remember correctly. This may be the only useful part of the project.

## Platform

Data gathering is the primary goal of this project. That said, the core technology of Trackmini lies in it's database. You can find the lightweight database model in the *Database* folder.

In order for users to be able to track things, they need to be able to access the platform from any device (such as iPhone on the go, computer at work, etc.). With cross-device support in mind, I wanted to create a generic data access layer so that any internet enabled device on the could add/retrieve the data.

## Data Access Layer REST API

The DAL (Data Access Layer) is written in PHP. It's accessible via a SOAP protocol using JSON messages. It contains generic methods and responses to allow CRUD on any table in the database leaving it up to the client application to parse and transform it. It is also possible to define custom CRUD behavior for specific tables, overriding the generic methods.


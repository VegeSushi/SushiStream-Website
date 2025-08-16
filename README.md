# SushiStream

An mjpeg video hosting page

## Admin info

### Creating master password

Create a .env file in the root and add this:

```env
MASTER_PASSWORD=YourMasterPassword
```

### Generating invite keys

Go to *http://127.0.0.1/admin* and authenticate with the master password

Then press the *Generate Key* button

### Database

Mongodb needs to be installed on the server

## API

API is avaible under *http://127.0.0.1/api*<br>
Currently you can only access videos under the *videos* page under the *api* page
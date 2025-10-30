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

### Video upload

The video upload requires FFmpeg to be installed system wide from the distro's package manager

## API

API is avaible under *http://127.0.0.1/api*<br>
Currently you can only access videos under the *videos* page under the *api* page

## Hosting

1. Clone the repo to /var/www/SushiStream-Website
2. Install Caddy, refer to (these instructions)[https://caddyserver.com/docs/install].
3. Put the contents of caddy/Caddyfile into /etc/caddy/Caddyfile
4. Restart Caddy using this command:
```bash
sudo systemctl restart caddy
```

### Notes

HTTPS is not supported due to our client's limitations, please use HTTP

## Clients:

(M5Cardputer)[https://docs.invidious.io/youtube-errors-explained/#po-token-initialization-taking-too-much-time-to-complete]
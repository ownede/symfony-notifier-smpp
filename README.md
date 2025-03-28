Symfony Notifier SMPP Bridge
============================

Provides [SMPP v3.4](https://smpp.org/) integration for Symfony Notifier.

DSN for SMPP
---

The DSN is used to configure the SMPP transport in your Symfony application. It should be set in your `.env` file or `config/packages/notifier.yaml` file.

### Example

```
SMPP_DSN=smpp://USERNAME:PASSWORD@HOSTNAME:PORT?sender=SENDER
```

where:
- `USERNAME` - your username definied by your SMPP provider
- `PASSWORD` - your password definied by your SMPP provider
- `HOSTNAME` - SMPP server hostname or IP address
- `PORT` - SMPP server port (default is 2775, optional)
- `SENDER` is your sender name that will appear as "from" in SMS (optional)


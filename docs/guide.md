# What is Orchestration?

We refer to orchestration as the ability to communicate with one or more
providers using a single api. The main goal is to assist you in decoupling
your services from the rest of your application. If done well, this will
improve maintainability, scalability & reliability of your application.

## Developer Experience

Orchestration is designed to improve the Developer Experience (DX) when
working with multiple services within your Laravel application.

- Auto generation of your service's foundation; All you need to do is
fill in the blanks.
- Multi driver support, for both, static & dynamic approaches. Or use
your own custom driver.
- Basic configurations out of the box to simplify orchestration between
multiple providers & merchants.


## Why Payavel?

Payavel began as an open-source Laravel package for payments with
[payavel/checkout](https://github.com/payavel/checkout). Later, we
released [payavel/subscription](https://github.com/payavel/subscription)
to compliment the checkout package. That's when we noticed both these
packages rely on a similar foundation, so we decided to extract it into the
[payavel/orchestration](https://github.com/payavel/orchestration) package 
so it can be leveraged by the Laravel community.

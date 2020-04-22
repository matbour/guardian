# Concepts

While Guardian is a library to handle JWT, we introduced some concepts to manipulate tokens more easily.

## Key
A **key** is composed of an algorithm and its parameters.
For example, a key which uses the `RS256` algorithm is RSA-based key and has a *size* expressed on bits.

The following table summarizes the key types, their available algorithms and parameters.

| Type                    | Available algorithms      | Parameters     |
|-------------------------|---------------------------|----------------|
| ECDSA (Elliptic curves) | `HS256`, `HS384`, `HS512` | `curve`        |
| EDDSA (Edward's curves) | `EDDSA`                   | `curve`        |
| HMAC                    | `HS256`, `HS384`, `HS512` | `size` in bits |
| RSA                     | `RS256`, `RS384`, `RS512` | `size` in bits |

## Claims

## Authority

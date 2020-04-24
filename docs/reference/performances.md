# Performances

## Methodology
Benchmarks are located in the `/benchmarks` directory and achieved with the [PHPBench library](https://github.com/phpbench/phpbench).

They are run on [GitHub Actions runners](https://help.github.com/en/actions/reference/software-installed-on-github-hosted-runners).
Since all servers have different hardware, the values provided are only indicative.

For each benchmark, we configured the runner to iterate 100 times on the tested feature excluding 10 "warmup interations".


## JWT Signature
Median duration for a simple payload signature.
All results are expressed in microseconds.

### ECDSA (Elliptic Curves Cryptography)
| Algorithm / Curve | P-256 | P-384   | P-521 |
|-------------------|-------|---------|-------|
| ES256             | 388.3 | 2,046.0 | 847.1 |
| ES384             | 388.3 | 2,041.0 | 862.2 |
| ES512             | 379.5 | 2,073.6 | 866.3 |

### EDDSA (Edward's Curve Cryptography)
| Algorithm / Curve | Ed25519 |
|-------------------|---------|
| EdDSA             | 235.6   |

### HMAC
| Algorithm / Key size in bits | 256   | 384   | 512   |
|------------------------------|-------|-------|-------|
| HS256                        | 187.7 | n/a   | n/a   |
| HS384                        | n/a   | 189.7 | n/a   |
| HS512                        | n/a   | n/a   | 191.4 |

### RSA
| Algorithm / Key size in bits | 2048     | 3072     | 4096     |
|------------------------------|----------|----------|----------|
| RS256                        | 46,114.9 | n/a      | n/a      |
| RS384                        | n/a      | 69,223.0 | n/a      |
| RS512                        | n/a      | n/a      | 97,678.6 |
| PS256                        | 1,858.1  | n/a      | n/a      |
| PS384                        | n/a      | 4,961.5  | n/a      |
| PS512                        | n/a      | n/a      | 10,154.6 |

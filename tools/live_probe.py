import urllib.parse
import urllib.request
import sys

sys.stdout.reconfigure(encoding="utf-8")
BASE = "http://localhost/donations-platform"

payloads = [
    ("sqli", "' OR '1'='1"),
    ("sqli", "'; DROP TABLE campaigns; --"),
    ("xss", "<script>alert(1)</script>"),
]

for kind, p in payloads:
    url = BASE + "/explore.php?q=" + urllib.parse.quote(p)
    try:
        r = urllib.request.urlopen(url, timeout=5)
        body = r.read().decode("utf-8", errors="replace")
        sql_err = "SQL" in body and ("syntax" in body.lower() or "mysql" in body.lower())
        xss_raw = p in body
        xss_esc = "&lt;script&gt;" in body
        print(f"[{kind}] {p[:35]}")
        print(f"  HTTP {r.status} | SQL leak: {sql_err} | XSS raw in HTML: {xss_raw} | escaped: {xss_esc}")
    except Exception as e:
        print(f"[{kind}] {p[:35]} -> {e}")

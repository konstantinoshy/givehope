#!/usr/bin/env python3
"""
GiveHope — SQL Injection payload runner (100+ payloads).
Produces evidence report for thesis appendix.

Usage (XAMPP running):
  python tools/sqli_payload_test.py
  python tools/sqli_payload_test.py --base http://localhost/donations-platform
"""
from __future__ import annotations

import argparse
import csv
import http.cookiejar
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from dataclasses import dataclass
from datetime import datetime, timezone
from pathlib import Path

# ---------------------------------------------------------------------------
# 120 common SQLi payloads (OWASP / sqlmap inspired, truncated for safety)
# ---------------------------------------------------------------------------
PAYLOADS: list[str] = [
    "' OR '1'='1",
    "' OR 1=1--",
    "' OR 1=1#",
    "' OR 1=1/*",
    "admin'--",
    "admin' #",
    "admin'/*",
    "' OR ''='",
    "' OR 'x'='x",
    "') OR ('1'='1",
    "') OR ('1'='1'--",
    "1' OR '1'='1",
    "1 OR 1=1",
    "1' OR '1'='1'--",
    "1' OR '1'='1'/*",
    "1' OR '1'='1'#",
    "' UNION SELECT NULL--",
    "' UNION SELECT NULL,NULL--",
    "' UNION SELECT NULL,NULL,NULL--",
    "1' UNION SELECT 1,2,3--",
    "' UNION ALL SELECT NULL--",
    "1; SELECT 1--",
    "1; DROP TABLE users--",
    "1'; DROP TABLE users--",
    "1'; DROP TABLE campaigns--",
    "'; WAITFOR DELAY '0:0:5'--",
    "1' AND SLEEP(5)--",
    "1' AND SLEEP(5)#",
    "1' AND 1=1--",
    "1' AND 1=2--",
    "' OR 1=1 LIMIT 1--",
    "' OR 1=1 LIMIT 1 OFFSET 1--",
    "1' ORDER BY 1--",
    "1' ORDER BY 10--",
    "1' ORDER BY 100--",
    "1' GROUP BY 1--",
    "1' HAVING 1=1--",
    "' OR username LIKE '%",
    "' OR email LIKE '%",
    "x' OR 1=1 OR 'x'='y",
    "x\" OR \"1\"=\"1",
    "x' OR 1=1 AND 'x'='x",
    "0 OR 1=1",
    "false OR true--",
    "' OR 'a'='a",
    "') OR ('a'='a",
    "1' AND '1'='1",
    "1' AND '1'='2",
    "' OR 1=1;--",
    "1'; EXEC xp_cmdshell('dir')--",
    "1' AND EXTRACTVALUE(1,CONCAT(0x7e,VERSION()))--",
    "1' AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT(VERSION(),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)--",
    "1' AND UPDATEXML(1,CONCAT(0x7e,VERSION()),1)--",
    "1' AND 1=CONVERT(int,(SELECT @@version))--",
    "1' AND 1=CAST((SELECT @@version) AS int)--",
    "' OR 1=1 INTO OUTFILE '/tmp/x'--",
    "1' OR 1=1 PROCEDURE ANALYSE()--",
    "1' OR '1'='1' UNION SELECT null,table_name FROM information_schema.tables--",
    "' UNION SELECT 1,load_file('/etc/passwd')--",
    "1' AND MID(VERSION(),1,1)='5",
    "1' AND SUBSTRING(@@version,1,1)='5",
    "1' AND ASCII(SUBSTRING((SELECT password_hash FROM users LIMIT 1),1,1))>0--",
    "1' OR EXISTS(SELECT * FROM users)--",
    "1' OR NOT EXISTS(SELECT * FROM users)--",
    "1' OR 1=1-- -",
    "1'/**/OR/**/1=1--",
    "1'%20OR%201=1--",
    "1'%09OR%091=1--",
    "1'%0AOR%0A1=1--",
    "1' OR 1=1; %00",
    "1\x00' OR 1=1--",
    "1' OR 1=1%23",
    "1' OR 1=1%2D%2D",
    "1' OR '1'='1'%00",
    "' OR 1=1%00",
    "1' OR 1=1;#",
    "1' OR 1=1;--%20",
    "1' OR 1=1 LIMIT 1;--",
    "1' OR 1=1 OFFSET 0;--",
    "1' RLIKE (SELECT 1)--",
    "1' REGEXP '^.*$'--",
    "1' LIKE '%'--",
    "1' NOT LIKE ''--",
    "1' IS NULL OR 1=1--",
    "1' BETWEEN 1 AND 2 OR 1=1--",
    "1' IN (1,2,3) OR 1=1--",
    "1' OR 1=1--%20%20",
    "1' OR 1=1--+",
    "1' OR 1=1%09--",
    "1' OR 1=1%0D%0A--",
    "1' OR 1=1%0A--",
    "1' OR 1=1%0C--",
    "1' OR 1=1%0B--",
    "1' OR 1=1%0D--",
    "1' OR 1=1%1A--",
    "1' OR 1=1%00--",
    "1' OR 1=1; EXEC('malicious')--",
    "1' OR 1=1; SHUTDOWN--",
    "1' OR 1=1; DELETE FROM users--",
    "1' OR 1=1; UPDATE users SET password_hash='x'--",
    "1' OR 1=1; INSERT INTO users VALUES(999,'h','h','h',1,NOW(),NULL)--",
    "1' OR 1=1; CREATE TABLE pwned(id INT)--",
    "1' OR 1=1; ALTER TABLE users ADD col VARCHAR(1)--",
    "1' OR 1=1; TRUNCATE TABLE donations--",
    "1' OR 1=1; GRANT ALL ON *.* TO 'x'@'%'--",
    "1' OR 1=1; REVOKE ALL ON *.* FROM 'root'@'localhost'--",
    "1' OR 1=1; SHOW DATABASES--",
    "1' OR 1=1; SHOW TABLES--",
    "1' OR 1=1; SELECT @@version--",
    "1' OR 1=1; SELECT user()--",
    "1' OR 1=1; SELECT database()--",
    "1' OR 1=1; SELECT LOAD_FILE('/etc/passwd')--",
    "1' OR 1=1; SELECT 1 INTO OUTFILE '/tmp/x'--",
    "1' OR 1=1; SELECT 1 INTO DUMPFILE '/tmp/x'--",
    "1' OR 1=1; SELECT 1 FROM dual--",
    "1' OR 1=1; SELECT 1 FROM information_schema.tables--",
    "1' OR 1=1; SELECT 1 FROM mysql.user--",
    "1' OR 1=1; SELECT 1 FROM pg_sleep(5)--",
    "1' OR 1=1; SELECT pg_sleep(5)--",
    "1' OR 1=1; SELECT sleep(5)--",
    "1' OR 1=1; SELECT benchmark(1000000,md5('x'))--",
    "1' OR 1=1; SELECT count(*) FROM users--",
    "1' OR 1=1; SELECT password_hash FROM users--",
    "1' OR 1=1; SELECT email FROM users--",
    "1' OR 1=1; SELECT * FROM users--",
    "1' OR 1=1; SELECT * FROM campaigns--",
    "1' OR 1=1; SELECT * FROM donations--",
    "1' OR 1=1; SELECT * FROM admins--",
    "1' OR 1=1; SELECT * FROM organizations--",
    "1' OR 1=1; SELECT * FROM categories--",
    "1' OR 1=1; SELECT * FROM reports--",
    "1' OR 1=1; SELECT * FROM messages--",
    "1' OR 1=1; SELECT * FROM gdpr_requests--",
    "1' OR 1=1; SELECT * FROM cookie_consents--",
    "1' OR 1=1; SELECT * FROM data_processing_log--",
    "1' OR 1=1; SELECT * FROM documents--",
    "1' OR 1=1; SELECT * FROM campaign_updates--",
]

SQL_ERROR_PATTERNS = [
    r"you have an error in your sql syntax",
    r"warning:\s*mysqli?",
    r"sqlstate\[",
    r"pdoexception",
    r"unclosed quotation mark",
    r"quoted string not properly terminated",
    r"syntax error.*sql",
    r"mysql_fetch",
    r"mysql_num_rows",
    r"fatal error.*mysql",
]

LOGIN_SUCCESS_MARKERS = [
    "my-campaigns.php",
    "dashboard/index.php",
    "location: http",
]


@dataclass
class TestResult:
    endpoint: str
    field: str
    payload: str
    http_status: int
    passed: bool  # True = vulnerability detected (BAD)
    reason: str


class SessionClient:
    def __init__(self, base: str) -> None:
        self.base = base.rstrip("/")
        self.jar = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))

    def get(self, path: str) -> tuple[int, str, dict[str, str]]:
        req = urllib.request.Request(self.base + path, method="GET")
        try:
            with self.opener.open(req, timeout=10) as resp:
                body = resp.read().decode("utf-8", errors="replace")
                headers = {k.lower(): v for k, v in resp.headers.items()}
                return resp.status, body, headers
        except urllib.error.HTTPError as e:
            body = e.read().decode("utf-8", errors="replace")
            headers = {k.lower(): v for k, v in e.headers.items()}
            return e.code, body, headers

    def post(self, path: str, data: dict[str, str]) -> tuple[int, str, dict[str, str]]:
        encoded = urllib.parse.urlencode(data).encode()
        req = urllib.request.Request(
            self.base + path,
            data=encoded,
            method="POST",
            headers={"Content-Type": "application/x-www-form-urlencoded"},
        )
        try:
            with self.opener.open(req, timeout=10) as resp:
                body = resp.read().decode("utf-8", errors="replace")
                headers = {k.lower(): v for k, v in resp.headers.items()}
                return resp.status, body, headers
        except urllib.error.HTTPError as e:
            body = e.read().decode("utf-8", errors="replace")
            headers = {k.lower(): v for k, v in e.headers.items()}
            return e.code, body, headers


def extract_csrf(html: str) -> str | None:
    m = re.search(r'name="csrf_token"\s+value="([^"]+)"', html)
    return m.group(1) if m else None


def analyze(status: int, body: str, headers: dict[str, str], check_login_bypass: bool, payload: str) -> tuple[bool, str]:
    """Return (passed=vulnerable, reason)."""
    lower = body.lower()
    payload_lower = payload.lower()

    for pat in SQL_ERROR_PATTERNS:
        if re.search(pat, lower):
            return True, f"SQL error pattern: {pat}"

    # Ignore generic words if they only appear as part of reflected payload text
    if payload_lower in lower and "syntax" not in lower and "sqlstate" not in lower:
        pass  # reflected search term, not an error

    if check_login_bypass:
        loc = headers.get("location", "")
        if any(m in loc.lower() for m in ["my-campaigns", "dashboard/index"]):
            return True, f"Login bypass redirect: {loc}"
        if status in (301, 302, 303, 307, 308) and "login" not in loc.lower():
            if "my-campaigns" in loc or "dashboard" in loc:
                return True, f"Unexpected redirect: {loc}"

    if status >= 500 and ("exception" in lower or "fatal" in lower):
        return True, f"HTTP {status} server error (possible injection)"

    return False, "No vulnerability indicator"


def test_explore_search(client: SessionClient, payload: str) -> TestResult:
    path = "/explore.php?q=" + urllib.parse.quote(payload)
    status, body, headers = client.get(path)
    passed, reason = analyze(status, body, headers, False, payload)
    return TestResult("GET /explore.php", "q", payload, status, passed, reason)


def test_login_email(client: SessionClient, payload: str) -> TestResult:
    status, html, _ = client.get("/login.php?type=user")
    token = extract_csrf(html)
    if not token:
        return TestResult("POST /login.php", "email", payload, status, False, "Could not obtain CSRF token")

    post_status, body, headers = client.post(
        "/login.php",
        {
            "type": "user",
            "email": payload,
            "password": "wrongpassword123",
            "csrf_token": token,
        },
    )
    passed, reason = analyze(post_status, body, headers, True, payload)
    return TestResult("POST /login.php", "email", payload, post_status, passed, reason)


def test_explore_cat(client: SessionClient, payload: str) -> TestResult:
    path = "/explore.php?cat=" + urllib.parse.quote(payload)
    status, body, headers = client.get(path)
    passed, reason = analyze(status, body, headers, False, payload)
    return TestResult("GET /explore.php", "cat", payload, status, passed, reason)


def main() -> int:
    sys.stdout.reconfigure(encoding="utf-8")
    parser = argparse.ArgumentParser()
    parser.add_argument("--base", default="http://localhost/donations-platform")
    parser.add_argument("--out", default="tools/sqli_test_report")
    args = parser.parse_args()

    client = SessionClient(args.base)
    results: list[TestResult] = []

    # Verify server
    try:
        s, _, _ = client.get("/index.php")
        if s != 200:
            print(f"Warning: index.php returned HTTP {s}")
    except Exception as e:
        print(f"ERROR: Cannot reach {args.base} — start XAMPP Apache/MySQL.\n{e}")
        return 1

    print(f"Running {len(PAYLOADS)} payloads × 3 endpoints = {len(PAYLOADS) * 3} tests...")
    print(f"Target: {args.base}\n")

    for p in PAYLOADS:
        results.append(test_explore_search(client, p))
        results.append(test_login_email(client, p))
        results.append(test_explore_cat(client, p))

    passed_count = sum(1 for r in results if r.passed)
    total = len(results)

    out_base = Path(__file__).resolve().parent / Path(args.out).name
    csv_path = out_base.with_suffix(".csv")
    md_path = out_base.with_suffix(".md")

    with csv_path.open("w", newline="", encoding="utf-8") as f:
        w = csv.writer(f)
        w.writerow(["endpoint", "field", "payload", "http_status", "vulnerable", "reason"])
        for r in results:
            w.writerow([r.endpoint, r.field, r.payload, r.http_status, r.passed, r.reason])

    ts = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M UTC")
    md_lines = [
        "# GiveHope SQL Injection Test Report",
        "",
        f"- **Date:** {ts}",
        f"- **Target:** {args.base}",
        f"- **Unique payloads:** {len(PAYLOADS)}",
        f"- **Total tests:** {total} (3 endpoints per payload)",
        f"- **Vulnerabilities found:** {passed_count}",
        f"- **Result:** {'FAIL — review flagged tests' if passed_count else 'PASS — 0 payloads succeeded'}",
        "",
        "## Endpoints tested",
        "",
        "| Endpoint | Field | Protection |",
        "|---|---|---|",
        "| GET /explore.php | q | PDO placeholder `:q1`, `:q2` |",
        "| POST /login.php | email | PDO placeholder `:e` + CSRF |",
        "| GET /explore.php | cat | `(int)` cast |",
        "",
        "## Summary",
        "",
    ]
    if passed_count:
        md_lines.append("### Flagged tests (require manual review)")
        md_lines.append("")
        for r in results:
            if r.passed:
                md_lines.append(f"- `{r.payload[:60]}` on {r.endpoint} ({r.field}): {r.reason}")
    else:
        md_lines.append(
            f"All **{total}** automated tests completed without SQL error leakage, "
            "login bypass, or server errors indicative of successful injection."
        )

    md_lines.extend(
        [
            "",
            "## Methodology",
            "",
            "1. Payload list based on OWASP SQL Injection cheat sheet and common sqlmap vectors.",
            "2. Each payload sent to three user-input surfaces.",
            "3. Response scanned for SQL error strings, login bypass redirects, HTTP 500.",
            "4. Full results in accompanying CSV file.",
            "",
            "## Limitations",
            "",
            "- Not a professional penetration test.",
            "- Blind/time-based SQLi (SLEEP) not reliably detected without timing analysis.",
            "- Admin-only endpoints not tested (require authenticated session).",
        ]
    )

    md_path.write_text("\n".join(md_lines), encoding="utf-8")

    print("=" * 60)
    print(f"Total tests:        {total}")
    print(f"Payloads:           {len(PAYLOADS)}")
    print(f"Vulnerabilities:    {passed_count}")
    print(f"CSV report:         {csv_path}")
    print(f"Markdown summary:   {md_path}")
    print("=" * 60)
    if passed_count == 0:
        print("PASS — 0 payloads succeeded (safe to cite with this report as evidence)")
    else:
        print("REVIEW — some tests flagged; inspect CSV before citing in thesis")
    return 0 if passed_count == 0 else 2


if __name__ == "__main__":
    raise SystemExit(main())

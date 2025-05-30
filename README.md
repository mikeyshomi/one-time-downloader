# one-time-downloader
PHP file-host with “one-time” link generator, upload a file via web form and instantly get a direct download URL without any confirmation screen (educational/demo only).

# One-Time Download Link Generator (Conceptual Overview)

![Download UI](/images/secure-file-hosting.png)

**Goal:** Outline secure, expiring URLs usable once without intermediate UI.

1. **Request file:** identify file path or ID.  
2. **Generate token:** random string (e.g. `bin2hex(random_bytes(16))`), store with file, expiry, unused status.  
3. **Deliver link:**  
   ```
   https://your.site/download.php?token=abcdef123456
   ```  
4. **On download:** validate token exists, unused, not expired; mark used; stream file or return error.  
5. **Cleanup:** scheduled job to remove expired tokens.

> ⚠️ Educational/demo purposes only. Raw code is not provided.

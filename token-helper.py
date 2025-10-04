import sys
import requests
import json

URL = "http://127.0.0.1:5566/rpa-manage.php"
HEADERS = {"Content-Type": "application/json"}

def get_token(agent_name):
    data = {"action": "get_token", "agent_name": agent_name}
    resp = requests.post(URL, headers=HEADERS, data=json.dumps(data))
    return resp.json()

def refresh_token(token):
    data = {"action": "refresh_token", "token": token}
    resp = requests.post(URL, headers=HEADERS, data=json.dumps(data))
    return resp.json()

def close_token(token):
    data = {"action": "close_token", "token": token}
    resp = requests.post(URL, headers=HEADERS, data=json.dumps(data))
    return resp.json()

def usage():
    print("Usage:")
    print("  python token-handler.py get <agent_name>")
    print("  python token-handler.py refresh <token>")
    print("  python token-handler.py close <token>")
    sys.exit(1)

def main():
    if len(sys.argv) < 2:
        usage()

    action = sys.argv[1].lower()

    if action == "get":
        if len(sys.argv) != 3:
            usage()
        agent_name = sys.argv[2]
        res = get_token(agent_name)
        print(json.dumps(res, ensure_ascii=False))

    elif action == "refresh":
        if len(sys.argv) != 3:
            usage()
        token = sys.argv[2]
        res = refresh_token(token)
        print(json.dumps(res, ensure_ascii=False))

    elif action == "close":
        if len(sys.argv) != 3:
            usage()
        token = sys.argv[2]
        res = close_token(token)
        print(json.dumps(res, ensure_ascii=False))

    else:
        usage()

if __name__ == "__main__":
    main()

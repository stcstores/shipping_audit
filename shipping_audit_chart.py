import matplotlib.pyplot as plt

import lstools


class Shipping_Service():

    def __init__(self, name):
        self.name = name
        self.shipments = []

    def add_shipment(self, sent, recieved, order_id):
        self.shipments.append(Shipment(self.name, sent, recieved, order_id))

    def get_shipping_times(self):
        shipping_times = []
        for shipment in self.shipments:
            shipping_times.append(shipment.shipment_time())
        return shipping_times

    def __getitem__(self, key):
        return self.shipments[key]

    def __iter__(self):
        return self.shipments


class Shipment():

    def __init__(self, service, sent, recieved, order_id):
        self.service = service
        self.sent = sent
        self.recieved = recieved
        self.order_number = order_id

    def shipment_time(self):
        td = self.recieved - self.sent
        return td.days


def remove_trailing_zero_from_list(array):
    if len(array) < 2:
        return array
    elif array[-1] != 0:
        return array
    elif array.count(0) == len(array):
        return array
    else:
        i = len(array) - 1
        while array[i] == 0:
            array.pop(i)
            i -= 1
    return array

db = lstools.DatabaseConnection(host='mysql.stcadmin.stcstores.co.uk',
                                user='seatontrading',
                                passwd='Cosworth1',
                                database='seatontrading')
results = db.query("SELECT * FROM shipping_audit")


shipping_services = {}

for result in results:
    sent = result[1]
    recieved = result[2]
    service = result[3]
    order_id = result[4]
    if service not in shipping_services:
        shipping_services[service] = Shipping_Service(service)
    shipping_services[service].add_shipment(sent, recieved, order_id)


plt.style.use('ggplot')

for service_name in shipping_services:
    fig = plt.figure()
    fig.suptitle(service_name, fontsize=20)
    plt.autoscale()
    service = shipping_services[service_name]
    shipping_times_for_days = []
    shipping_times = service.get_shipping_times()
    for i in range(1000):
        shipping_times_for_days.append(shipping_times.count(i))

    shipping_times_for_days = remove_trailing_zero_from_list(
        shipping_times_for_days)
    day_counts = list(range(len(shipping_times_for_days)))
    plt.xticks(list(range(1, len(shipping_times_for_days))), fontsize=20)
    plt.yticks(list(range(1, max(shipping_times_for_days) + 1)), fontsize=20)
    ax = plt.axes()
    ax.yaxis.grid(color='black')
    ax.xaxis.grid(False)
    plt.axis([0, max(day_counts) + 1, 0, max(shipping_times_for_days) + 1])
    plt.bar(day_counts, shipping_times_for_days, align="center")
    plt.xlabel("Days in transit")
    fig.savefig('charts/' + service_name + '.png', bbox_inches='tight')

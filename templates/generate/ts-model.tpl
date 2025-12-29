export interface ActivityLogModel {
	event: string;
	info: string;
	timestamp: Date;
	userId: number;
}

export class ActivityLogBase {
	public event: string;
	public info: string;
	public timestamp: Date;
	public userId: number;

	constructor(model?: ActivityLogModel) {
		this.event = model.event ?? '';
		this.info = model.info ?? '';
		this.timestamp = model.timestamp ?? new Date();
		this.userId = model.userId ?? 0;

		return;
	}
}

export interface ApiSessionModel {
	address: string;
	created: Date;
	hostname: string;
	id: number;
	token: string;
	userId: number;
}

export class ApiSessionBase {
	public address: string;
	public created: Date;
	public hostname: string;
	public id: number;
	public token: string;
	public userId: number;

	constructor(model?: ApiSessionModel) {
		this.address = model.address ?? '';
		this.created = model.created ?? new Date();
		this.hostname = model.hostname ?? '';
		this.id = model.id ?? 0;
		this.token = model.token ?? '';
		this.userId = model.userId ?? 0;

		return;
	}
}
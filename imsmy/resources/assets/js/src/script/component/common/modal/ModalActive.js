import React, { Component } from 'react'
import { Modal, Button, message } from 'antd'
const confirm = Modal.confirm
import Fetch from 'utils/fetch'

class ModalActive extends Component {
	constructor(props){
    super(props)
     this.state = {

  	}
  }

  showActiveAndDisableConfirm=()=>{
      confirm({
        title: `${this.props.btnName}`,
        content: `确定将这${this.props.id.length!==undefined? this.props.id.length : ''}
									条数据设置为${this.props.btnName}吗？`,
        okText: '确定',
        cancelText: '取消',
        onOk:()=>{

					//设置为精选
					if(this.props.activeUri!==undefined && this.props.activeUri!=='' && this.props.btnName==='精选'){
						this.handleChangeActive()
					}
					//取消精选
					if(this.props.cancelUri!==undefined && this.props.cancelUri!=='' && this.props.btnName === '取消精选'){
						this.handleChangeCancelActive()
					}
					//停用
					if(this.props.disableUri!==undefined && this.props.disableUri!=='' && this.props.btnName === '停用'){
						this.handleChangeDisable()
					}
        },
        onCancel:()=>{
          message.success('已取消');
        }
      })

  }
 handleChangeActive=()=>{
	 let formData= new FormData()
	 if(this.props.id.length !==undefined){
		 const IdLength = this.props.id.length
		 if(IdLength ===1){
			 const newId = this.props.id.toString()
			 formData.append('id',newId)
		 }else if(IdLength >1){
			 const newId = this.props.id.join('|')
				formData.append('id',newId)
		 }
	 }else{
		 formData.append('id',this.props.id)
	 }
	 Fetch.post({
		 uri:this.props.activeUri,
		 callback:(res)=>{
			 console.log(res,'精选');
			 if(res.message && res.message==='加精成功'){
				 message.success(res.message)
				 if(this.props.RefreshTableState){
					 this.props.RefreshTableState(true)
				 }
			 }else{
				 message.error(res.message || res.error);
				 if(this.props.RefreshTableState){
					 this.props.RefreshTableState(true)
				 }
			 }
		 },
		 formData:formData
	 })
 }
 handleChangeCancelActive=()=>{
	 //取消精选和下架都是同一个接口
 	let formData= new FormData()
 	if(this.props.id.length !==undefined){
 		const IdLength = this.props.id.length
 		if(IdLength ===1){
 			const cancelNewId = this.props.id.toString()
 			formData.append('id',cancelNewId)
 		}else if(IdLength >1){
 			const cancelNewId = this.props.id.join('|')
 			 formData.append('id',cancelNewId)
 		}
 	}else{
 		formData.append('id',this.props.id)
 	}
 	Fetch.post({
 		uri:this.props.cancelUri,
 		callback:(res)=>{
 			console.log(res,'取消精选');
 			if(res.message && res.message==='下架成功'){
 				message.success(res.message)
 				if(this.props.RefreshTableState){
 					this.props.RefreshTableState(true)
 				}
 			}else{
 				message.error(res.message || res.error);
 				if(this.props.RefreshTableState){
 					this.props.RefreshTableState(true)
 				}
 			}
 		},
 		formData:formData
 	})
 }
 handleChangeDisable=()=>{
 	let formData= new FormData()
 	if(this.props.id.length !==undefined){
 		const IdLength = this.props.id.length
 		if(IdLength ===1){
 			const disableNewId = this.props.id.toString()
 			formData.append('id',disableNewId)
 		}else if(IdLength >1){
 			const disableNewId = this.props.id.join('|')
 			 formData.append('id',disableNewId)
 		}
 	}else{
 		formData.append('id',this.props.id)
 	}
 	Fetch.post({
 		uri:this.props.disableUri,
 		callback:(res)=>{
 			console.log(res,'停用');
 			if(res.message && res.message==='下架成功'){
 				message.success(res.message)
 				if(this.props.RefreshTableState){
 					this.props.RefreshTableState(true)
 				}
 			}else{
 				message.error(res.message || res.error);
 				if(this.props.RefreshTableState){
 					this.props.RefreshTableState(true)
 				}
 			}
 		},
 		formData:formData
 	})
 }
  render() {
    return (
			<div>

       <Button style={{color:`${this.props.disableUri !==''? '#ff000' : '#4a4a4a'}`}}
				 onClick={this.showActiveAndDisableConfirm} >
       		{this.props.btnName}
      	</Button>
			</div>
    );
  }

}


export default ModalActive

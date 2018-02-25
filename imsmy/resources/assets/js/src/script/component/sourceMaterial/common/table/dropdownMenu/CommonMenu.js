import React, { Component } from 'react'
import { Button, Menu, Dropdown, Icon} from 'antd'

import ModalTransfer from '../../../../common/modal/ModalTransfer'
import ModalTop from '../../../../common/modal/ModalTop'
import ModalScreen from '../../../../common/modal/ModalScreen'

//下拉菜单
class CommonMenu extends Component {
  constructor(props){
    super(props)
     this.state={
       visible:false,
       // menuItem:this.props.menuName
     }
     // console.log(this.props,'commen');
  }
  render() {
    const tableID = this.props.operation? this.props.record : this.props.SelectAll
    return (
      <Dropdown overlay={
        <Menu className="dropMenu" style={{textAlign:"center"}}>

              <Menu.Item  key="1">
                <ModalTransfer id={tableID}
                  visible={this.state.visible}
                  uri={this.props.ModalPopUri}
                />
              </Menu.Item>

              <Menu.Item  key="2">
                  <Button>热门</Button>
              </Menu.Item>

              <Menu.Item  key="3">
                <ModalTop id={tableID} visible={this.state.visible}
                  uri={this.props.ModalTopUri}/>
                </Menu.Item>

              <Menu.Item  key="4">
                <ModalScreen id={tableID} visible={this.state.visible} />
              </Menu.Item>


        </Menu>}
        trigger={['click']}>
        <Button type={this.props.operation ? "" : "primary"} >
            {this.props.operation ? "操作" : "批量操作"}
              <Icon type="down" />
        </Button>
      </Dropdown>
      )
  }

}

export default CommonMenu
